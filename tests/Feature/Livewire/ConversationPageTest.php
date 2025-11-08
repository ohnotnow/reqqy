<?php

use App\Events\ConversationSignedOff;
use App\Jobs\GenerateConversationTitleJob;
use App\Livewire\ConversationPage;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses()->group('livewire');

beforeEach(function () {
    config([
        'reqqy.llm.default' => 'anthropic/claude-3-5-sonnet-20241022',
        'reqqy.llm.small' => 'anthropic/claude-3-haiku-20240307',
    ]);
});

it('can render the conversation page', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('conversation', ['conversation_id' => $conversation->id]))
        ->assertSuccessful()
        ->assertSeeLivewire(ConversationPage::class);
});

it('creates a new conversation when conversation_id is not provided', function () {
    $user = User::factory()->create();

    expect(Conversation::count())->toBe(0);

    $this->actingAs($user)
        ->get(route('conversation'));

    expect(Conversation::count())->toBe(1);

    $conversation = Conversation::first();
    expect($conversation->user_id)->toBe($user->id);
    expect($conversation->application_id)->toBeNull();
});

it('creates a new conversation with application_id when provided', function () {
    $user = User::factory()->create();
    $application = Application::factory()->create();

    expect(Conversation::count())->toBe(0);

    $this->actingAs($user)
        ->get(route('conversation', ['application_id' => $application->id]));

    expect(Conversation::count())->toBe(1);

    $conversation = Conversation::first();
    expect($conversation->user_id)->toBe($user->id);
    expect($conversation->application_id)->toBe($application->id);
});

it('loads existing conversation when conversation_id is provided', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('conversation', ['conversation_id' => $conversation->id]))
        ->assertSuccessful();

    expect(Conversation::count())->toBe(1);
});

it('prevents users from accessing other users conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->get(route('conversation', ['conversation_id' => $conversation->id]))
        ->assertForbidden();
});

it('can send a message and receive llm response', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Claude is the best')
        ->withUsage(new Usage(10, 5));

    Prism::fake([$fakeResponse]);

    expect(Message::count())->toBe(0);

    $component = Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'Hello, how are you?')
        ->call('sendMessage')
        ->assertSet('messageContent', '')
        ->assertSet('isAwaitingResponse', true);

    expect(Message::count())->toBe(1);

    $userMessage = Message::where('user_id', $user->id)->first();
    expect($userMessage->content)->toBe('Hello, how are you?');
    expect($userMessage->conversation_id)->toBe($conversation->id);

    $component->call('handleUserMessageCreated', $userMessage->id)
        ->assertSet('isAwaitingResponse', false);

    expect(Message::count())->toBe(2);

    $llmMessage = Message::whereNull('user_id')->first();
    expect($llmMessage->content)->toBe('Claude is the best');
    expect($llmMessage->conversation_id)->toBe($conversation->id);
});

it('validates message content is required', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', '')
        ->call('sendMessage')
        ->assertHasErrors(['messageContent' => 'required']);

    expect(Message::count())->toBe(0);
});

it('validates message content does not exceed max length', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    $longMessage = str_repeat('a', 10001);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', $longMessage)
        ->call('sendMessage')
        ->assertHasErrors(['messageContent' => 'max']);

    expect(Message::count())->toBe(0);
});

it('prevents sending messages after conversation is signed off', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'signed_off_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'This should not be sent')
        ->call('sendMessage');

    expect(Message::count())->toBe(0);
});

it('can sign off a conversation', function () {
    Event::fake();

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    expect($conversation->fresh()->signed_off_at)->toBeNull();
    expect(Message::count())->toBe(0);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->call('signOff');

    expect($conversation->fresh()->signed_off_at)->not->toBeNull();
    expect(Message::count())->toBe(1);

    $message = Message::first();
    expect($message->user_id)->toBeNull();
    expect($message->conversation_id)->toBe($conversation->id);
    expect($message->content)->toContain('Thank you for providing your requirements');

    Event::assertDispatched(ConversationSignedOff::class, function ($event) use ($conversation) {
        return $event->conversation->id === $conversation->id;
    });
});

it('prevents signing off a conversation that is already signed off', function () {
    $user = User::factory()->create();
    $signedOffAt = now()->subHour();
    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'signed_off_at' => $signedOffAt,
    ]);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->call('signOff');

    expect($conversation->fresh()->signed_off_at->toDateTimeString())->toBe($signedOffAt->toDateTimeString());
    expect(Message::count())->toBe(0);
});

it('displays existing messages in the conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'User message content',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'LLM message content',
    ]);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->assertSee('User message content')
        ->assertSee('LLM message content');
});

it('passes conversation history to llm when generating response', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'First user message',
        'created_at' => now()->subMinutes(2),
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'First LLM response',
        'created_at' => now()->subMinutes(1),
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Claude is the best')
        ->withUsage(new Usage(20, 8));

    $fake = Prism::fake([$fakeResponse]);

    $component = Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'Second user message')
        ->call('sendMessage')
        ->assertSet('isAwaitingResponse', true);

    expect(Message::count())->toBe(3);

    $userMessage = Message::where('user_id', $user->id)->latest()->first();

    $component->call('handleUserMessageCreated', $userMessage->id)
        ->assertSet('isAwaitingResponse', false);

    expect(Message::count())->toBe(4);

    $llmMessages = Message::whereNull('user_id')->orderBy('created_at', 'desc')->get();
    expect($llmMessages->first()->content)->toBe('Claude is the best');

    $fake->assertRequest(function (array $requests) {
        expect($requests)->toHaveCount(1);

        /** @var \Prism\Prism\Text\Request $request */
        $request = $requests[0];
        $messages = $request->messages();

        expect($messages)->toHaveCount(3);
        expect($messages[0]->content)->toBe('First user message');
        expect($messages[1]->content)->toBe('First LLM response');
        expect($messages[2]->content)->toBe('Second user message');
    });
});

it('starts new conversations with default title', function () {
    $user = User::factory()->create();

    expect(Conversation::count())->toBe(0);

    $this->actingAs($user)
        ->get(route('conversation'));

    expect(Conversation::count())->toBe(1);

    $conversation = Conversation::first();
    expect($conversation->title)->toBe('New conversation');
});

it('dispatches title generation job when threshold reached', function () {
    Queue::fake();

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Test response')
        ->withUsage(new Usage(10, 5));

    Prism::fake([$fakeResponse, $fakeResponse, $fakeResponse, $fakeResponse]);

    $component = Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id]);

    // Send first message (count = 1, no job dispatched)
    $component->set('messageContent', 'First message')
        ->call('sendMessage');

    Queue::assertNotPushed(GenerateConversationTitleJob::class);

    // Generate LLM response (count = 2, no job dispatched)
    $userMessage1 = Message::where('user_id', $user->id)->latest()->first();
    $component->call('handleUserMessageCreated', $userMessage1->id);

    Queue::assertNotPushed(GenerateConversationTitleJob::class);

    // Send second message (count = 3, no job dispatched)
    $component->set('messageContent', 'Second message')
        ->call('sendMessage');

    Queue::assertNotPushed(GenerateConversationTitleJob::class);

    // Generate LLM response (count = 4, job should be dispatched)
    $userMessage2 = Message::where('user_id', $user->id)->latest()->first();
    $component->call('handleUserMessageCreated', $userMessage2->id);

    // Now threshold is reached on next user message (count = 5)
    $component->set('messageContent', 'Third message')
        ->call('sendMessage');

    Queue::assertPushed(GenerateConversationTitleJob::class, function ($job) use ($conversation) {
        return $job->conversation->id === $conversation->id;
    });
});

it('does not dispatch job before threshold', function () {
    Queue::fake();

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'First message')
        ->call('sendMessage');

    expect(Message::count())->toBe(1);
    Queue::assertNotPushed(GenerateConversationTitleJob::class);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'Second message')
        ->call('sendMessage');

    expect(Message::count())->toBe(2);
    Queue::assertNotPushed(GenerateConversationTitleJob::class);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'Third message')
        ->call('sendMessage');

    expect(Message::count())->toBe(3);
    Queue::assertNotPushed(GenerateConversationTitleJob::class);
});

it('does not dispatch job if title already customized', function () {
    Queue::fake();

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'title' => 'Custom Title',
    ]);

    // Create 3 existing messages to be near threshold
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Message 1',
    ]);
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'Response 1',
    ]);
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Message 2',
    ]);

    // Send 4th message to reach threshold
    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'Fourth message')
        ->call('sendMessage');

    expect(Message::count())->toBe(4);
    Queue::assertNotPushed(GenerateConversationTitleJob::class);
});
