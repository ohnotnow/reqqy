<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\LlmService;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    config(['reqqy.llm' => 'anthropic/claude-3-5-sonnet-20241022']);
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
    config(['reqqy.llm' => 'anthropic/claude-3-5-sonnet-20241022']);

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
    config(['reqqy.llm' => 'openai/gpt-4']);

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
    config(['reqqy.llm' => 'openrouter/anthropic/claude-3.5-sonnet']);

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
    config(['reqqy.llm' => '']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $service = new LlmService;
    $messages = collect();

    expect(fn () => $service->generateResponse($conversation, $messages))
        ->toThrow(InvalidArgumentException::class, 'LLM configuration is not set. Please set REQQY_LLM in your .env file.');
});

it('throws exception when llm config is null', function () {
    config(['reqqy.llm' => null]);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $service = new LlmService;
    $messages = collect();

    expect(fn () => $service->generateResponse($conversation, $messages))
        ->toThrow(InvalidArgumentException::class, 'LLM configuration is not set. Please set REQQY_LLM in your .env file.');
});

it('throws exception when llm config is missing slash separator', function () {
    config(['reqqy.llm' => 'anthropic-claude-3-5-sonnet']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $service = new LlmService;
    $messages = collect();

    expect(fn () => $service->generateResponse($conversation, $messages))
        ->toThrow(InvalidArgumentException::class, 'LLM configuration must be in the format "provider/model"');
});

it('throws exception for unsupported provider', function () {
    config(['reqqy.llm' => 'gemini/gemini-pro']);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create(['user_id' => $user->id]);
    $service = new LlmService;
    $messages = collect();

    expect(fn () => $service->generateResponse($conversation, $messages))
        ->toThrow(InvalidArgumentException::class, 'Unsupported provider: gemini. Supported providers are: anthropic, openai, openrouter.');
});

it('handles case-insensitive provider names', function () {
    config(['reqqy.llm' => 'ANTHROPIC/claude-3-5-sonnet-20241022']);

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
    $conversation = Conversation::factory()->create(['user_id' => $user->id, 'application_id' => null]);

    $service = new LlmService;
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('renderChatPrompt');
    $method->setAccessible(true);

    $prompt = $method->invoke($service, $conversation);

    expect($prompt)->toContain('Context: New Application Request');
    expect($prompt)->toContain('completely new application');
    expect($prompt)->not->toContain('Feature Request for Existing Application');
});

it('renders chat prompt for feature request with application context', function () {
    $user = User::factory()->create();
    $application = \App\Models\Application::factory()->create([
        'name' => 'My CRM App',
        'short_description' => 'A customer relationship management system',
        'overview' => 'This is a Laravel-based CRM with contact management and sales pipeline features.',
    ]);

    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'application_id' => $application->id,
    ]);

    $service = new LlmService;
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('renderChatPrompt');
    $method->setAccessible(true);

    $prompt = $method->invoke($service, $conversation);

    expect($prompt)->toContain('Context: Feature Request for Existing Application');
    expect($prompt)->toContain('My CRM App');
    expect($prompt)->toContain('A customer relationship management system');
    expect($prompt)->toContain('This is a Laravel-based CRM with contact management and sales pipeline features.');
    expect($prompt)->not->toContain('New Application Request');
});

it('renders chat prompt for feature request without optional application fields', function () {
    $user = User::factory()->create();
    $application = \App\Models\Application::factory()->create([
        'name' => 'Basic App',
        'short_description' => null,
        'overview' => null,
    ]);

    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'application_id' => $application->id,
    ]);

    $service = new LlmService;
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('renderChatPrompt');
    $method->setAccessible(true);

    $prompt = $method->invoke($service, $conversation);

    expect($prompt)->toContain('Context: Feature Request for Existing Application');
    expect($prompt)->toContain('Basic App');
    expect($prompt)->not->toContain('Application Description:');
    expect($prompt)->not->toContain('Application Overview:');
});
