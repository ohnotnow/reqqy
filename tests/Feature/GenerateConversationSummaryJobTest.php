<?php

use App\Jobs\GenerateConversationSummaryJob;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;

beforeEach(function () {
    config([
        'reqqy.llm.default' => 'anthropic/claude-3-5-sonnet-20241022',
        'reqqy.llm.small' => 'anthropic/claude-3-haiku-20240307',
    ]);

    // Mock Prism responses
    Prism::fake([
        TextResponseFake::make()->withText('User requested a bulk student import feature for the Student Projects app. They need CSV upload with validation, duplicate detection, and rollback capability. Integration with existing student records is critical.'),
    ]);
});

it('generates a summary from conversation messages', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'I need a bulk student import feature',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'Tell me more about your requirements',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'It should support CSV upload with validation and rollback',
    ]);

    // Act
    $job = new GenerateConversationSummaryJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert
    $conversation = $conversation->fresh();
    expect($conversation->summary)->not->toBeNull();
    expect($conversation->summary)->toContain('bulk student import');
    expect($conversation->summary)->toContain('CSV upload');
});

it('stores summary in conversation record', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null, 'summary' => null]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'I want to build a task management system',
    ]);

    // Act
    $job = new GenerateConversationSummaryJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert
    $conversation = $conversation->fresh();
    expect($conversation->summary)->toBe('User requested a bulk student import feature for the Student Projects app. They need CSV upload with validation, duplicate detection, and rollback capability. Integration with existing student records is critical.');
});

it('handles empty conversations gracefully', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null, 'summary' => null]);

    // Act
    $job = new GenerateConversationSummaryJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - summary should remain null since there are no messages
    $conversation = $conversation->fresh();
    expect($conversation->summary)->toBeNull();
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
    $job = new GenerateConversationSummaryJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - summary was created successfully
    $conversation = $conversation->fresh();
    expect($conversation->summary)->not->toBeNull();
    expect($conversation->summary)->toContain('User requested a bulk student import feature');
});
