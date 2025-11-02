<?php

use App\Jobs\ResearchAlternativesJob;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewDocumentCreated;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('creates a stub research document for the conversation', function () {
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
        'content' => 'I need a project management tool',
    ]);

    // Act
    $job = new ResearchAlternativesJob($conversation);
    $job->handle();

    // Assert
    assertDatabaseCount('documents', 1);

    assertDatabaseHas('documents', [
        'conversation_id' => $conversation->id,
        'name' => 'Existing Solution Research',
    ]);

    $document = Document::first();
    expect($document->conversation_id)->toBe($conversation->id);
    expect($document->name)->toBe('Existing Solution Research');
    expect($document->content)->not->toBeEmpty();

    // Assert notifications sent to admin users only
    Notification::assertSentTo($adminUser, NewDocumentCreated::class);
    Notification::assertNotSentTo($user, NewDocumentCreated::class);
});

it('notifies all admin users when research document is created', function () {
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
        'content' => 'I need a CRM system',
    ]);

    // Act
    $job = new ResearchAlternativesJob($conversation);
    $job->handle();

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
    $job = new ResearchAlternativesJob($conversation);
    $job->handle();

    // Assert - notification includes document with conversation relationship
    Notification::assertSentTo(
        $adminUser,
        NewDocumentCreated::class,
        function ($notification) use ($conversation) {
            $document = $notification->document;

            return $document->conversation_id === $conversation->id
                && $document->name === 'Existing Solution Research';
        }
    );
});
