<?php

use App\Jobs\GenerateFeatureRequestPrdJob;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewDocumentCreated;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    config([
        'reqqy.llm.default' => 'anthropic/claude-3-5-sonnet-20241022',
        'reqqy.llm.small' => 'anthropic/claude-3-haiku-20240307',
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
    expect($document->content)->toContain('My App');
    expect($document->content)->toContain('LLM generation pending - this is a stub document');
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

    // Assert - document is still created with stub content
    assertDatabaseCount('documents', 1);
    $document = Document::first();
    expect($document->content)->toContain('Feature Request Document');
    expect($document->content)->toContain('Test App');
    expect($document->content)->toContain('LLM generation pending - this is a stub document');
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

it('includes application name in feature request document', function () {
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

    // Assert
    $document = Document::first();
    expect($document->content)->toContain('Awesome Application');
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

    // Assert - document is still created with fallback application name
    $document = Document::first();
    expect($document->content)->toContain('Unknown Application');
});
