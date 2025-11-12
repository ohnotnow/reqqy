<?php

use App\Jobs\UpdateUserMemoryJob;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserMemory;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;

beforeEach(function () {
    config([
        'reqqy.llm.default' => 'anthropic/claude-3-5-sonnet-20241022',
        'reqqy.llm.small' => 'anthropic/claude-3-haiku-20240307',
    ]);

    // Mock Prism responses
    Prism::fake([
        TextResponseFake::make()->withText('Billy works in higher education IT, manages multiple student-facing systems, values simplicity and maintainability. Refers to the student records export as "the main export". Uses Laravel and Livewire tech stack with Lando for local development.'),
    ]);
});

it('creates new memory for user without existing memory', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'I need a student import feature for our system',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'Tell me more about your requirements',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'We use Laravel and Livewire, and we call our main export "the main export"',
    ]);

    // Assert no memory exists yet
    expect($user->memory)->toBeNull();

    // Act
    $job = new UpdateUserMemoryJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert
    $user = $user->fresh();
    expect($user->memory)->not->toBeNull();
    expect($user->memory->memory_content)->toContain('higher education');
    expect($user->memory->memory_content)->toContain('Laravel');
});

it('updates existing memory for user with memory', function () {
    // Arrange
    $user = User::factory()->create();

    // Create existing memory
    UserMemory::factory()->create([
        'user_id' => $user->id,
        'memory_content' => 'Billy works in education IT and uses Laravel.',
    ]);

    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'I want to add a feature to the main export',
    ]);

    // Act
    $job = new UpdateUserMemoryJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - memory was updated, not created new
    expect(UserMemory::count())->toBe(1);

    $user = $user->fresh();
    expect($user->memory->memory_content)->toBe('Billy works in higher education IT, manages multiple student-facing systems, values simplicity and maintainability. Refers to the student records export as "the main export". Uses Laravel and Livewire tech stack with Lando for local development.');
});

it('handles empty conversations gracefully', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    // Act
    $job = new UpdateUserMemoryJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - memory should not be created since there are no messages
    expect($user->memory)->toBeNull();
});

it('uses all messages in chronological order', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    // Create messages out of order to test ordering
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Third message',
        'created_at' => now()->addMinutes(2),
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'First message',
        'created_at' => now(),
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'Second message',
        'created_at' => now()->addMinutes(1),
    ]);

    // Act
    $job = new UpdateUserMemoryJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - memory was created successfully
    $user = $user->fresh();
    expect($user->memory)->not->toBeNull();
    expect($user->memory->memory_content)->toContain('Billy works in higher education');
});
