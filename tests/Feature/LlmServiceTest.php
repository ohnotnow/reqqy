<?php

use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\LlmService;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    config([
        'reqqy.llm.default' => 'anthropic/claude-3-5-sonnet-20241022',
        'reqqy.llm.small' => 'anthropic/claude-3-haiku-20240307',
    ]);
});

it('can generate a response from conversation messages', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Hello',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'Hi there!',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'How are you?',
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('I am doing great, thank you!')
        ->withUsage(new Usage(10, 20));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $messages = $conversation->messages()->orderBy('created_at')->get();
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('I am doing great, thank you!');
});

it('handles empty conversation history', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $messages = collect();

    $fakeResponse = TextResponseFake::make()
        ->withText('Hello! How can I help you today?')
        ->withUsage(new Usage(5, 15));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('Hello! How can I help you today?');
});

it('handles conversation with multiple messages', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'First question',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'First answer',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Second question',
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Second answer based on context')
        ->withUsage(new Usage(8, 25));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $messages = $conversation->messages()->orderBy('created_at')->get();
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('Second answer based on context');
});

it('preserves message order in conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Message 1',
        'created_at' => now()->subMinutes(5),
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'Response 1',
        'created_at' => now()->subMinutes(4),
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Message 2',
        'created_at' => now()->subMinutes(3),
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Response 2')
        ->withUsage(new Usage(15, 10));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $messages = $conversation->messages()->orderBy('created_at')->get();
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('Response 2');
    expect($messages->count())->toBe(3);
    expect($messages->first()->content)->toBe('Message 1');
    expect($messages->last()->content)->toBe('Message 2');
});

it('works with anthropic provider', function () {
    config(['reqqy.llm.default' => 'anthropic/claude-3-5-sonnet-20241022']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $messages = collect();

    $fakeResponse = TextResponseFake::make()
        ->withText('Anthropic response')
        ->withUsage(new Usage(5, 10));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('Anthropic response');
});

it('works with openai provider', function () {
    config(['reqqy.llm.default' => 'openai/gpt-4']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $messages = collect();

    $fakeResponse = TextResponseFake::make()
        ->withText('OpenAI response')
        ->withUsage(new Usage(5, 10));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('OpenAI response');
});

it('works with openrouter provider', function () {
    config(['reqqy.llm.default' => 'openrouter/anthropic/claude-3.5-sonnet']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $messages = collect();

    $fakeResponse = TextResponseFake::make()
        ->withText('OpenRouter response')
        ->withUsage(new Usage(5, 10));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('OpenRouter response');
});

it('throws exception when llm config is empty', function () {
    config(['reqqy.llm.default' => '']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $service = new LlmService;
    $messages = collect();

    expect(fn () => $service->generateResponse($conversation, $messages))
        ->toThrow(InvalidArgumentException::class, 'LLM configuration is not set. Please set REQQY_LLM in your .env file.');
});

it('throws exception when llm config is null', function () {
    config(['reqqy.llm.default' => null]);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $service = new LlmService;
    $messages = collect();

    expect(fn () => $service->generateResponse($conversation, $messages))
        ->toThrow(InvalidArgumentException::class, 'LLM configuration is not set. Please set REQQY_LLM in your .env file.');
});

it('throws exception when llm config is missing slash separator', function () {
    config(['reqqy.llm.default' => 'anthropic-claude-3-5-sonnet']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $service = new LlmService;
    $messages = collect();

    expect(fn () => $service->generateResponse($conversation, $messages))
        ->toThrow(InvalidArgumentException::class, 'LLM configuration must be in the format "provider/model"');
});

it('throws exception for unsupported provider', function () {
    config(['reqqy.llm.default' => 'gemini/gemini-pro']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $service = new LlmService;
    $messages = collect();

    expect(fn () => $service->generateResponse($conversation, $messages))
        ->toThrow(InvalidArgumentException::class, 'Unsupported provider: gemini. Supported providers are: anthropic, openai, openrouter.');
});

it('handles case-insensitive provider names', function () {
    config(['reqqy.llm.default' => 'ANTHROPIC/claude-3-5-sonnet-20241022']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $messages = collect();

    $fakeResponse = TextResponseFake::make()
        ->withText('Case insensitive works')
        ->withUsage(new Usage(5, 10));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('Case insensitive works');
});

it('uses default chat prompt when no custom prompt provided', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id, 'application_id' => null]);
    $messages = collect();

    $fakeResponse = TextResponseFake::make()
        ->withText('Default prompt response')
        ->withUsage(new Usage(5, 10));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $response = $service->generateResponse($conversation, $messages);

    expect($response)->toBe('Default prompt response');
});

it('accepts custom system prompt', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $messages = collect();

    $customPrompt = 'You are a technical writer. Generate a PRD based on the conversation.';

    $fakeResponse = TextResponseFake::make()
        ->withText('Custom prompt response')
        ->withUsage(new Usage(5, 10));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $response = $service->generateResponse($conversation, $messages, $customPrompt);

    expect($response)->toBe('Custom prompt response');
});

it('renders chat prompt for new application request', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'application_id' => null,
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Prompt check response');

    $fake = Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $service->generateResponse($conversation, collect());

    $fake->assertRequest(function (array $requests) {
        expect($requests)->toHaveCount(1);

        /** @var \Prism\Prism\Text\Request $request */
        $request = $requests[0];
        $systemPrompts = $request->systemPrompts();

        expect($systemPrompts)->toHaveCount(1);

        $prompt = $systemPrompts[0]->content;

        expect($prompt)->toContain('**Context:** This is a new application idea.');
        expect($prompt)->not->toContain('feature request');
    });
});

it('renders chat prompt for feature request with application context', function () {
    $user = User::factory()->create();
    $application = Application::factory()->create([
        'name' => 'My CRM App',
        'short_description' => 'A customer relationship management system',
        'overview' => 'This is a Laravel-based CRM with contact management and sales pipeline features.',
    ]);

    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'application_id' => $application->id,
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Prompt check response');

    $fake = Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $service->generateResponse($conversation->fresh('application'), collect());

    $fake->assertRequest(function (array $requests) {
        expect($requests)->toHaveCount(1);

        /** @var \Prism\Prism\Text\Request $request */
        $request = $requests[0];
        $systemPrompts = $request->systemPrompts();

        expect($systemPrompts)->toHaveCount(1);

        $prompt = $systemPrompts[0]->content;

        expect($prompt)->toContain('**Context:** This is a feature request for "My CRM App".');
        expect($prompt)->toContain('A customer relationship management system');
        expect($prompt)->not->toContain('This is a new application idea.');
    });
});

it('renders chat prompt for feature request without optional application fields', function () {
    $user = User::factory()->create();
    $application = Application::factory()->create([
        'name' => 'Basic App',
        'short_description' => null,
        'overview' => null,
    ]);

    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'application_id' => $application->id,
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Prompt check response');

    $fake = Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $service->generateResponse($conversation->fresh('application'), collect());

    $fake->assertRequest(function (array $requests) {
        expect($requests)->toHaveCount(1);

        /** @var \Prism\Prism\Text\Request $request */
        $request = $requests[0];
        $systemPrompts = $request->systemPrompts();

        expect($systemPrompts)->toHaveCount(1);

        $prompt = $systemPrompts[0]->content;

        expect($prompt)->toContain('**Context:** This is a feature request for "Basic App".');
        expect($prompt)->not->toContain('This is a new application idea.');
        expect($prompt)->not->toContain('A customer relationship management system');
    });
});
