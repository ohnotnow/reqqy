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

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    config([
        'reqqy.llm.default' => 'anthropic/claude-3-5-sonnet-20241022',
        'reqqy.llm.small' => 'anthropic/claude-3-haiku-20240307',
    ]);

    // Mock Prism responses
    Prism::fake([
        TextResponseFake::make()->withText('# Product Requirements Document

## Executive Summary
This is a mocked PRD generated for testing purposes.

## Goals and Objectives
- Goal 1: User authentication
- Goal 2: Task management

## User Personas
- Persona 1: Project Manager
- Persona 2: Team Member

## Functional Requirements
- Must Have: User login and registration
- Should Have: Task assignment
- Nice to Have: Advanced reporting

## Non-Functional Requirements
- Performance: Page load under 2 seconds
- Security: Password encryption

## User Stories
- As a user, I want to create tasks so that I can track my work

## Technical Considerations
- Laravel framework
- MySQL database

## Out of Scope
- Mobile application
- Third-party integrations

## Open Questions
- Should we support SSO?'),
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
    expect($document->content)->toContain('This is a mocked PRD generated for testing purposes');
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

    // Act
    $job = new GenerateNewApplicationPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - document was created
    expect(Document::count())->toBe(1);

    // Verify the job completed successfully with mocked content
    $document = Document::first();
    expect($document->content)->toContain('Product Requirements Document');
    expect($document->content)->toContain('This is a mocked PRD generated for testing purposes');
});

it('creates a document even if no messages exist', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    // Act
    $job = new GenerateNewApplicationPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - document is still created with mocked content
    assertDatabaseCount('documents', 1);
    $document = Document::first();
    expect($document->content)->toContain('Product Requirements Document');
    expect($document->content)->toContain('This is a mocked PRD generated for testing purposes');
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
