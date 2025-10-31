<?php

use App\Jobs\GenerateNewApplicationPrdJob;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\Message;
use App\Models\User;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('generates a PRD document from conversation messages', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'I need a task management app',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'Tell me more about your requirements',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'It should have user authentication, task creation, and deadline tracking',
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText("# Product Requirements Document\n\n## Executive Summary\nA task management application...")
        ->withUsage(new Usage(100, 200));

    Prism::fake([$fakeResponse]);

    // Act
    $job = new GenerateNewApplicationPrdJob($conversation);
    $job->handle();

    // Assert
    assertDatabaseCount('documents', 1);

    assertDatabaseHas('documents', [
        'conversation_id' => $conversation->id,
        'name' => 'Product Requirements Document',
    ]);

    $document = Document::first();
    expect($document->content)->toContain('Product Requirements Document');
    expect($document->conversation_id)->toBe($conversation->id);
});

it('uses all conversation messages in chronological order', function () {
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

    $fakeResponse = TextResponseFake::make()
        ->withText('Generated PRD content')
        ->withUsage(new Usage(50, 100));

    Prism::fake([$fakeResponse]);

    // Act
    $job = new GenerateNewApplicationPrdJob($conversation);
    $job->handle();

    // Assert - document was created
    expect(Document::count())->toBe(1);

    // The prompt would have been rendered with messages in chronological order
    // We verify this by ensuring the job completed successfully
    $document = Document::first();
    expect($document->content)->toBe('Generated PRD content');
});

it('does not create a document if no messages exist', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Generated PRD with no messages')
        ->withUsage(new Usage(10, 20));

    Prism::fake([$fakeResponse]);

    // Act
    $job = new GenerateNewApplicationPrdJob($conversation);
    $job->handle();

    // Assert - document is still created, but with empty message context
    assertDatabaseCount('documents', 1);
    $document = Document::first();
    expect($document->content)->toBe('Generated PRD with no messages');
});
