<?php

use App\Livewire\SettingsPage;
use App\Models\Application;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders successfully', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->assertStatus(200);
});

it('displays all applications', function () {
    $user = User::factory()->create();

    $application1 = Application::factory()->create(['name' => 'App One']);
    $application2 = Application::factory()->create(['name' => 'App Two']);
    $application3 = Application::factory()->create(['name' => 'App Three']);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->assertSee('App One')
        ->assertSee('App Two')
        ->assertSee('App Three');
});

it('shows empty state when no applications exist', function () {
    $user = User::factory()->create();

    expect(Application::count())->toBe(0);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->assertSee('No applications yet')
        ->assertSee('Get started by adding your first Laravel application');
});

it('can create a new application', function () {
    $user = User::factory()->create();

    expect(Application::count())->toBe(0);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->set('name', 'My New App')
        ->set('short_description', 'A short description')
        ->set('overview', 'A detailed overview')
        ->set('is_automated', true)
        ->set('status', 'Active')
        ->set('url', 'https://example.com')
        ->set('repo', 'https://github.com/user/repo')
        ->call('saveApplication')
        ->assertHasNoErrors();

    expect(Application::count())->toBe(1);

    $application = Application::first();
    expect($application->name)->toBe('My New App');
    expect($application->short_description)->toBe('A short description');
    expect($application->overview)->toBe('A detailed overview');
    expect($application->is_automated)->toBeTrue();
    expect($application->status)->toBe('Active');
    expect($application->url)->toBe('https://example.com');
    expect($application->repo)->toBe('https://github.com/user/repo');
});

it('resets form after creating application', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->set('name', 'My New App')
        ->set('short_description', 'A description')
        ->set('status', 'Active')
        ->call('saveApplication')
        ->assertSet('name', '')
        ->assertSet('short_description', '')
        ->assertSet('status', '')
        ->assertSet('editingApplicationId', null);
});

it('validates required fields when creating application', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->set('name', '')
        ->set('status', '')
        ->call('saveApplication')
        ->assertHasErrors(['name', 'status']);

    expect(Application::count())->toBe(0);
});

it('allows nullable fields when creating application', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->set('name', 'Minimal App')
        ->set('status', 'Development')
        ->set('short_description', '')
        ->set('overview', '')
        ->set('url', '')
        ->set('repo', '')
        ->call('saveApplication')
        ->assertHasNoErrors();

    $application = Application::first();
    expect($application->name)->toBe('Minimal App');
    expect($application->status)->toBe('Development');
    expect($application->short_description)->toBe('');
    expect($application->overview)->toBe('');
    expect($application->url)->toBe('');
    expect($application->repo)->toBe('');
});

it('can load application data for editing', function () {
    $user = User::factory()->create();

    $application = Application::factory()->create([
        'name' => 'Original App',
        'short_description' => 'Original description',
        'overview' => 'Original overview',
        'is_automated' => true,
        'status' => 'Active',
        'url' => 'https://original.com',
        'repo' => 'https://github.com/original/repo',
    ]);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->call('editApplication', $application->id)
        ->assertSet('editingApplicationId', $application->id)
        ->assertSet('name', 'Original App')
        ->assertSet('short_description', 'Original description')
        ->assertSet('overview', 'Original overview')
        ->assertSet('is_automated', true)
        ->assertSet('status', 'Active')
        ->assertSet('url', 'https://original.com')
        ->assertSet('repo', 'https://github.com/original/repo');
});

it('can update an existing application', function () {
    $user = User::factory()->create();

    $application = Application::factory()->create([
        'name' => 'Original Name',
        'status' => 'Development',
    ]);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->call('editApplication', $application->id)
        ->set('name', 'Updated Name')
        ->set('status', 'Production')
        ->set('short_description', 'Updated description')
        ->call('updateApplication')
        ->assertHasNoErrors();

    $updatedApplication = $application->fresh();
    expect($updatedApplication->name)->toBe('Updated Name');
    expect($updatedApplication->status)->toBe('Production');
    expect($updatedApplication->short_description)->toBe('Updated description');
    expect($updatedApplication->name)->not->toBe('Original Name');
});

it('resets form after updating application', function () {
    $user = User::factory()->create();

    $application = Application::factory()->create();

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->call('editApplication', $application->id)
        ->set('name', 'Updated Name')
        ->set('status', 'Active')
        ->call('updateApplication')
        ->assertSet('editingApplicationId', null)
        ->assertSet('name', '')
        ->assertSet('status', '');
});

it('validates required fields when updating application', function () {
    $user = User::factory()->create();

    $application = Application::factory()->create([
        'name' => 'Original Name',
        'status' => 'Active',
    ]);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->call('editApplication', $application->id)
        ->set('name', '')
        ->set('status', '')
        ->call('updateApplication')
        ->assertHasErrors(['name', 'status']);

    $unchangedApplication = $application->fresh();
    expect($unchangedApplication->name)->toBe('Original Name');
    expect($unchangedApplication->status)->toBe('Active');
});

it('can delete an application', function () {
    $user = User::factory()->create();

    $application = Application::factory()->create(['name' => 'To Be Deleted']);

    expect(Application::count())->toBe(1);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->call('deleteApplication', $application->id)
        ->assertHasNoErrors();

    expect(Application::count())->toBe(0);
    expect(Application::find($application->id))->toBeNull();
});

it('does not delete other applications when deleting one', function () {
    $user = User::factory()->create();

    $application1 = Application::factory()->create(['name' => 'Keep This']);
    $application2 = Application::factory()->create(['name' => 'Delete This']);
    $application3 = Application::factory()->create(['name' => 'Keep This Too']);

    expect(Application::count())->toBe(3);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->call('deleteApplication', $application2->id);

    expect(Application::count())->toBe(2);
    expect(Application::find($application1->id))->not->toBeNull();
    expect(Application::find($application3->id))->not->toBeNull();
    expect(Application::find($application2->id))->toBeNull();
});

it('throws exception when trying to edit non-existent application', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->call('editApplication', 999);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('throws exception when trying to update non-existent application', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->set('editingApplicationId', 999)
        ->set('name', 'Test')
        ->set('status', 'Active')
        ->call('updateApplication');
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('throws exception when trying to delete non-existent application', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->call('deleteApplication', 999);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('displays application with all fields populated', function () {
    $user = User::factory()->create();

    $application = Application::factory()->create([
        'name' => 'Full App',
        'short_description' => 'Short desc',
        'overview' => 'Full overview',
        'is_automated' => true,
        'status' => 'Production',
        'url' => 'https://full.app',
        'repo' => 'https://github.com/user/full',
    ]);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->assertSee('Full App')
        ->assertSee('Short desc')
        ->assertSee('Production')
        ->assertSee('Automated')
        ->assertSee('https://full.app')
        ->assertSee('https://github.com/user/full');
});

it('displays application with minimal fields', function () {
    $user = User::factory()->create();

    $application = Application::factory()->create([
        'name' => 'Minimal App',
        'short_description' => null,
        'overview' => null,
        'is_automated' => false,
        'status' => 'Development',
        'url' => null,
        'repo' => null,
    ]);

    actingAs($user);

    Livewire::test(SettingsPage::class)
        ->assertSee('Minimal App')
        ->assertSee('Development');

    // Verify the application doesn't show automated badge
    expect($application->is_automated)->toBeFalse();
});
