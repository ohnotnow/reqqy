<?php

use App\ApplicationCategory;
use App\ConversationStatus;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewProposedApplicationCreated;
use Illuminate\Support\Facades\Notification;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;

test('it creates proposed application when conversation is approved', function () {
    Notification::fake();
    Prism::fake([
        TextResponseFake::make()->withText('Vintage Guitar Marketplace'),
    ]);

    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Pending,
        'application_id' => null,
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $conversation->user_id,
        'content' => 'I want to build a marketplace for vintage guitars',
    ]);

    $conversation->status = ConversationStatus::Approved;
    $conversation->save();

    $proposedApp = Application::where('category', ApplicationCategory::Proposed)->first();
    expect($proposedApp)->not->toBeNull();
    expect($proposedApp->source_conversation_id)->toBe($conversation->id);
    expect($proposedApp->name)->toBe('Vintage Guitar Marketplace');

    $conversation = $conversation->fresh();
    expect($conversation->application_id)->toBe($proposedApp->id);
});

test('it does not create proposed application if conversation already has application', function () {
    $existingApp = Application::factory()->create();
    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Pending,
        'application_id' => $existingApp->id,
    ]);

    $conversation->status = ConversationStatus::Approved;
    $conversation->save();

    $proposedApps = Application::where('category', ApplicationCategory::Proposed)->get();
    expect($proposedApps)->toHaveCount(0);
});

test('it does not create proposed application if status changes to rejected', function () {
    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Pending,
        'application_id' => null,
    ]);

    $conversation->status = ConversationStatus::Rejected;
    $conversation->save();

    $proposedApps = Application::where('category', ApplicationCategory::Proposed)->get();
    expect($proposedApps)->toHaveCount(0);
});

test('it does not create proposed application if status changes to completed', function () {
    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Pending,
        'application_id' => null,
    ]);

    $conversation->status = ConversationStatus::Completed;
    $conversation->save();

    $proposedApps = Application::where('category', ApplicationCategory::Proposed)->get();
    expect($proposedApps)->toHaveCount(0);
});

test('it does not create proposed application if status does not change', function () {
    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Approved,
        'application_id' => null,
    ]);

    $conversation->touch();

    $proposedApps = Application::where('category', ApplicationCategory::Proposed)->get();
    expect($proposedApps)->toHaveCount(0);
});

test('it extracts application name using LLM', function () {
    Notification::fake();
    Prism::fake([
        TextResponseFake::make()->withText('Recipe Sharing Platform'),
    ]);

    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Pending,
        'application_id' => null,
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $conversation->user_id,
        'content' => 'I want to build a recipe sharing platform for home chefs',
    ]);

    $conversation->status = ConversationStatus::Approved;
    $conversation->save();

    $proposedApp = Application::where('category', ApplicationCategory::Proposed)->first();
    expect($proposedApp->name)->toBe('Recipe Sharing Platform');
});

test('it uses fallback name when no user messages exist', function () {
    Notification::fake();

    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Pending,
        'application_id' => null,
    ]);

    $conversation->status = ConversationStatus::Approved;
    $conversation->save();

    $proposedApp = Application::where('category', ApplicationCategory::Proposed)->first();
    expect($proposedApp->name)->toBe('New Application Proposal');
});

test('it notifies all admin users when proposed application is created', function () {
    Notification::fake();
    Prism::fake([
        TextResponseFake::make()->withText('Task Management App'),
    ]);

    $admin1 = User::factory()->create(['is_admin' => true]);
    $admin2 = User::factory()->create(['is_admin' => true]);
    $regularUser = User::factory()->create(['is_admin' => false]);

    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Pending,
        'application_id' => null,
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $conversation->user_id,
        'content' => 'Build a task management app',
    ]);

    $conversation->status = ConversationStatus::Approved;
    $conversation->save();

    Notification::assertSentTo([$admin1, $admin2], NewProposedApplicationCreated::class);
    Notification::assertNotSentTo($regularUser, NewProposedApplicationCreated::class);
});

test('it includes conversation link in notification', function () {
    Notification::fake();
    Prism::fake([
        TextResponseFake::make()->withText('Project Tracker'),
    ]);

    User::factory()->create(['is_admin' => true]);

    $conversation = Conversation::factory()->create([
        'status' => ConversationStatus::Pending,
        'application_id' => null,
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $conversation->user_id,
        'content' => 'Build a project tracker',
    ]);

    $conversation->status = ConversationStatus::Approved;
    $conversation->save();

    Notification::assertSentTo(
        User::where('is_admin', true)->get(),
        function (NewProposedApplicationCreated $notification) use ($conversation) {
            $proposedApp = Application::where('category', ApplicationCategory::Proposed)->first();

            return $notification->application->id === $proposedApp->id
                && $notification->application->source_conversation_id === $conversation->id;
        }
    );
});
