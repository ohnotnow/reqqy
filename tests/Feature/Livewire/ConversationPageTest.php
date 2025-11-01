<?php

use App\Livewire\ConversationPage;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Livewire\Livewire;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses()->group('livewire');

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
        ->assertNotFound();
});

it('can send a message and receive llm response', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    $fakeResponse = TextResponseFake::make()
        ->withText('This is the LLM response')
        ->withUsage(new Usage(10, 20));

    Prism::fake([$fakeResponse]);

    expect(Message::count())->toBe(0);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'Hello, how are you?')
        ->call('sendMessage')
        ->assertSet('messageContent', '');

    expect(Message::count())->toBe(2);

    $userMessage = Message::where('user_id', $user->id)->first();
    expect($userMessage->content)->toBe('Hello, how are you?');
    expect($userMessage->conversation_id)->toBe($conversation->id);

    $llmMessage = Message::whereNull('user_id')->first();
    expect($llmMessage->content)->toBe('This is the LLM response');
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
        ->withText('Second LLM response based on history')
        ->withUsage(new Usage(15, 30));

    Prism::fake([$fakeResponse]);

    Livewire::actingAs($user)
        ->test(ConversationPage::class, ['conversation_id' => $conversation->id])
        ->set('messageContent', 'Second user message')
        ->call('sendMessage');

    expect(Message::count())->toBe(4);

    $llmMessages = Message::whereNull('user_id')->orderBy('created_at', 'desc')->get();
    expect($llmMessages->first()->content)->toBe('Second LLM response based on history');
});
