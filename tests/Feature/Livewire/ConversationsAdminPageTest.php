<?php

use App\Livewire\ConversationsAdminPage;
use App\Models\Conversation;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders successfully for admin users', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    actingAs($admin);

    Livewire::test(ConversationsAdminPage::class)
        ->assertStatus(200);
});

it('denies access to non-admin users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    actingAs($user);

    Livewire::test(ConversationsAdminPage::class)
        ->assertForbidden();
});

it('displays all conversations', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();

    Conversation::factory()->create([
        'user_id' => $user->id,
        'application_id' => null,
    ]);

    Conversation::factory()->create(['user_id' => $user->id]);

    actingAs($admin);

    Livewire::test(ConversationsAdminPage::class)
        ->assertSee($user->username)
        ->assertSee('Feature Request')
        ->assertSee('New Application');
});

it('shows empty state when no conversations exist', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    expect(Conversation::count())->toBe(0);

    actingAs($admin);

    Livewire::test(ConversationsAdminPage::class)
        ->assertSee('No conversations yet')
        ->assertSee('Conversations will appear here once users start requesting features or applications');
});

it('displays conversation status badges', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();

    Conversation::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    actingAs($admin);

    Livewire::test(ConversationsAdminPage::class)
        ->assertSee('Pending');
});

it('displays message and document counts', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();

    $conversation = Conversation::factory()->create(['user_id' => $user->id]);

    actingAs($admin);

    Livewire::test(ConversationsAdminPage::class)
        ->assertSee('Messages:')
        ->assertSee('Documents:');
});

it('orders conversations by most recent first', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();

    $older = Conversation::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDays(2),
    ]);

    $newer = Conversation::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDay(),
    ]);

    actingAs($admin);

    $component = Livewire::test(ConversationsAdminPage::class);

    $conversations = $component->viewData('conversations');

    expect($conversations->first()->id)->toBe($newer->id);
    expect($conversations->last()->id)->toBe($older->id);
});

it('eager loads relationships', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();

    Conversation::factory()->create(['user_id' => $user->id]);

    actingAs($admin);

    $component = Livewire::test(ConversationsAdminPage::class);

    $conversations = $component->viewData('conversations');

    expect($conversations->first()->relationLoaded('user'))->toBeTrue();
    expect($conversations->first()->relationLoaded('application'))->toBeTrue();
    expect($conversations->first()->relationLoaded('messages'))->toBeTrue();
    expect($conversations->first()->relationLoaded('documents'))->toBeTrue();
});
