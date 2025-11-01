<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\LlmService;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

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
    $response = $service->generateResponse($messages);

    expect($response)->toBe('I am doing great, thank you!');
});

it('handles empty conversation history', function () {
    $messages = collect();

    $fakeResponse = TextResponseFake::make()
        ->withText('Hello! How can I help you today?')
        ->withUsage(new Usage(5, 15));

    Prism::fake([$fakeResponse]);

    $service = new LlmService;
    $response = $service->generateResponse($messages);

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
    $response = $service->generateResponse($messages);

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
    $response = $service->generateResponse($messages);

    expect($response)->toBe('Response 2');
    expect($messages->count())->toBe(3);
    expect($messages->first()->content)->toBe('Message 1');
    expect($messages->last()->content)->toBe('Message 2');
});
