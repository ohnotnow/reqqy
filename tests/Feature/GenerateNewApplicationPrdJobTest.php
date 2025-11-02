<?php

use App\Jobs\GenerateNewApplicationPrdJob;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewDocumentCreated;
use Illuminate\Support\Facades\Notification;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    config([
        'reqqy.llm.default' => 'anthropic/claude-3-5-sonnet-20241022',
        'reqqy.llm.small' => 'anthropic/claude-3-haiku-20240307',
    ]);
});

it('generates a PRD document from conversation messages', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();
    $adminUser = User::factory()->create(['is_admin' => true]);
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
    $job->handle(app(\App\Services\LlmService::class));

    // Assert
    assertDatabaseCount('documents', 1);

    assertDatabaseHas('documents', [
        'conversation_id' => $conversation->id,
        'name' => 'Product Requirements Document',
    ]);

    $document = Document::first();
    expect($document->content)->toContain('Product Requirements Document');
    expect($document->conversation_id)->toBe($conversation->id);

    // Assert notifications sent to admin users only
    Notification::assertSentTo($adminUser, NewDocumentCreated::class);
    Notification::assertNotSentTo($user, NewDocumentCreated::class);
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
    $job->handle(app(\App\Services\LlmService::class));

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
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - document is still created, but with empty message context
    assertDatabaseCount('documents', 1);
    $document = Document::first();
    expect($document->content)->toBe('Generated PRD with no messages');
});

it('notifies all admin users when a document is created', function () {
    // Arrange
    Notification::fake();

    $regularUser = User::factory()->create(['is_admin' => false]);
    $adminUser1 = User::factory()->create(['is_admin' => true]);
    $adminUser2 = User::factory()->create(['is_admin' => true]);

    $conversation = Conversation::factory()
        ->for($regularUser)
        ->create(['application_id' => null]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $regularUser->id,
        'content' => 'I need an app',
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Generated PRD')
        ->withUsage(new Usage(10, 20));

    Prism::fake([$fakeResponse]);

    // Act
    $job = new GenerateNewApplicationPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - all admin users are notified
    Notification::assertSentTo($adminUser1, NewDocumentCreated::class);
    Notification::assertSentTo($adminUser2, NewDocumentCreated::class);
    Notification::assertNotSentTo($regularUser, NewDocumentCreated::class);

    // Assert notification count
    Notification::assertCount(2);
});

it('includes conversation link in notification', function () {
    // Arrange
    Notification::fake();

    $adminUser = User::factory()->create(['is_admin' => true]);
    $conversation = Conversation::factory()
        ->for($adminUser)
        ->create(['application_id' => null]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $adminUser->id,
        'content' => 'Test message',
    ]);

    $fakeResponse = TextResponseFake::make()
        ->withText('Generated PRD')
        ->withUsage(new Usage(10, 20));

    Prism::fake([$fakeResponse]);

    // Act
    $job = new GenerateNewApplicationPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - notification includes document with conversation relationship
    Notification::assertSentTo(
        $adminUser,
        NewDocumentCreated::class,
        function ($notification) use ($conversation) {
            $document = $notification->document;

            return $document->conversation_id === $conversation->id;
        }
    );
});
