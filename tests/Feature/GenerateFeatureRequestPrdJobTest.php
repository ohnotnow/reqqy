<?php

use App\Jobs\GenerateFeatureRequestPrdJob;
use App\Models\Application;
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
        TextResponseFake::make()->withText('# Feature Request Document

## Feature Summary
This is a mocked feature request document generated for testing purposes.

## Problem Statement
Users need the ability to toggle between light and dark themes to reduce eye strain.

## Proposed Solution
Implement a theme switcher that persists user preference.

## User Stories
- As a user, I want to toggle dark mode so that I can reduce eye strain
- As a user, I want my theme preference to be remembered

## Acceptance Criteria
- User can toggle between light and dark themes
- Theme preference is persisted across sessions
- All UI components support both themes

## Integration Points
- Existing user settings system
- Layout and component styling

## Technical Considerations
- CSS variables for theme colors
- LocalStorage or database for preference storage
- Tailwind dark mode utilities

## UI/UX Requirements
- Toggle switch in settings or navigation bar
- Smooth transition between themes

## Testing Requirements
- Unit tests for theme persistence logic
- Feature tests for theme switching workflow
- Manual testing across different components

## Out of Scope
- Custom color themes beyond light/dark
- Per-page theme preferences

## Open Questions
- Should we support system theme detection?'),
    ]);
});

it('generates a feature request document from conversation messages', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();
    $adminUser = User::factory()->create(['is_admin' => true]);
    $application = Application::factory()->create(['name' => 'My App']);
    $conversation = Conversation::factory()
        ->for($user)
        ->for($application)
        ->create();

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'I need a dark mode feature',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => null,
        'content' => 'Tell me more about your requirements',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Users should be able to toggle between light and dark themes',
    ]);

    // Act
    $job = new GenerateFeatureRequestPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert
    assertDatabaseCount('documents', 1);

    assertDatabaseHas('documents', [
        'conversation_id' => $conversation->id,
        'name' => 'Feature Request Document',
    ]);

    $document = Document::first();
    expect($document->content)->toContain('Feature Request Document');
    expect($document->content)->toContain('This is a mocked feature request document generated for testing purposes');
    expect($document->conversation_id)->toBe($conversation->id);

    // Assert notifications sent to admin users only
    Notification::assertSentTo($adminUser, NewDocumentCreated::class);
    Notification::assertNotSentTo($user, NewDocumentCreated::class);
});

it('creates a feature request document even if no messages exist', function () {
    // Arrange
    $user = User::factory()->create();
    $application = Application::factory()->create(['name' => 'Test App']);
    $conversation = Conversation::factory()
        ->for($user)
        ->for($application)
        ->create();

    // Act
    $job = new GenerateFeatureRequestPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - document is still created with mocked content
    assertDatabaseCount('documents', 1);
    $document = Document::first();
    expect($document->content)->toContain('Feature Request Document');
    expect($document->content)->toContain('This is a mocked feature request document generated for testing purposes');
});

it('notifies all admin users when a feature request document is created', function () {
    // Arrange
    Notification::fake();

    $regularUser = User::factory()->create(['is_admin' => false]);
    $adminUser1 = User::factory()->create(['is_admin' => true]);
    $adminUser2 = User::factory()->create(['is_admin' => true]);

    $application = Application::factory()->create();
    $conversation = Conversation::factory()
        ->for($regularUser)
        ->for($application)
        ->create();

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $regularUser->id,
        'content' => 'I need a new feature',
    ]);

    // Act
    $job = new GenerateFeatureRequestPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - all admin users are notified
    Notification::assertSentTo($adminUser1, NewDocumentCreated::class);
    Notification::assertSentTo($adminUser2, NewDocumentCreated::class);
    Notification::assertNotSentTo($regularUser, NewDocumentCreated::class);

    // Assert notification count
    Notification::assertCount(2);
});

it('generates feature request document for specific application', function () {
    // Arrange
    $user = User::factory()->create();
    $application = Application::factory()->create(['name' => 'Awesome Application']);
    $conversation = Conversation::factory()
        ->for($user)
        ->for($application)
        ->create();

    // Act
    $job = new GenerateFeatureRequestPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - document is created with mocked content
    $document = Document::first();
    expect($document->content)->toContain('Feature Request Document');
    expect($document->content)->toContain('This is a mocked feature request document generated for testing purposes');
    expect($document->conversation->application->name)->toBe('Awesome Application');
});

it('handles conversations without an application', function () {
    // Arrange
    $user = User::factory()->create();
    $conversation = Conversation::factory()
        ->for($user)
        ->create(['application_id' => null]);

    // Act
    $job = new GenerateFeatureRequestPrdJob($conversation);
    $job->handle(app(\App\Services\LlmService::class));

    // Assert - document is still created with mocked content
    $document = Document::first();
    expect($document->content)->toContain('Feature Request Document');
    expect($document->content)->toContain('This is a mocked feature request document generated for testing purposes');
    expect($document->conversation->application_id)->toBeNull();
});
