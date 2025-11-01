<?php

use App\Models\Application;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

it('updates application overview from .llm.md file for a specific app', function () {
    // Arrange
    File::shouldReceive('exists')
        ->with('/path/to/repo/.llm.md')
        ->andReturn(true);

    File::shouldReceive('get')
        ->with('/path/to/repo/.llm.md')
        ->andReturn('# My Application Overview');

    $app = Application::factory()->create([
        'name' => 'Test App',
        'repo' => 'file:///path/to/repo',
        'is_automated' => true,
        'overview' => null,
    ]);

    // Act
    artisan('reqqy:get-application-info', ['--app-id' => $app->id])
        ->assertSuccessful();

    // Assert
    expect($app->fresh()->overview)->toBe('# My Application Overview');
});

it('sets overview to error message when .llm.md does not exist', function () {
    // Arrange
    File::shouldReceive('exists')
        ->with('/path/to/repo/.llm.md')
        ->andReturn(false);

    $app = Application::factory()->create([
        'name' => 'Test App',
        'repo' => 'file:///path/to/repo',
        'is_automated' => true,
        'overview' => null,
    ]);

    // Act
    artisan('reqqy:get-application-info', ['--app-id' => $app->id])
        ->assertSuccessful();

    // Assert
    expect($app->fresh()->overview)->toBe('.llm.md does not exist');
});

it('returns failure when app-id does not exist', function () {
    // Act & Assert
    artisan('reqqy:get-application-info', ['--app-id' => 999])
        ->assertFailed();
});

it('processes all automated applications with --all-apps flag', function () {
    // Arrange
    File::shouldReceive('exists')
        ->andReturn(true);

    File::shouldReceive('get')
        ->andReturn('# Application Overview');

    $automatedApp1 = Application::factory()->create([
        'name' => 'Automated App 1',
        'repo' => 'file:///path/to/repo1',
        'is_automated' => true,
        'overview' => null,
    ]);

    $automatedApp2 = Application::factory()->create([
        'name' => 'Automated App 2',
        'repo' => 'file:///path/to/repo2',
        'is_automated' => true,
        'overview' => null,
    ]);

    $manualApp = Application::factory()->create([
        'name' => 'Manual App',
        'repo' => 'file:///path/to/repo3',
        'is_automated' => false,
        'overview' => null,
    ]);

    // Act
    artisan('reqqy:get-application-info', ['--all-apps' => true])
        ->assertSuccessful();

    // Assert - automated apps are updated
    expect($automatedApp1->fresh()->overview)->toBe('# Application Overview');
    expect($automatedApp2->fresh()->overview)->toBe('# Application Overview');

    // Assert - manual app is not updated
    expect($manualApp->fresh()->overview)->toBeNull();
});

it('returns success with message when no automated applications exist', function () {
    // Arrange - create only manual apps
    Application::factory()->create([
        'name' => 'Manual App',
        'repo' => 'file:///path/to/repo',
        'is_automated' => false,
    ]);

    // Act & Assert
    artisan('reqqy:get-application-info', ['--all-apps' => true])
        ->expectsOutput('No automated applications found')
        ->assertSuccessful();
});

it('returns failure when neither --app-id nor --all-apps is provided', function () {
    // Act & Assert
    artisan('reqqy:get-application-info')
        ->expectsOutput('You must specify either --app-id or --all-apps')
        ->assertFailed();
});

it('handles repos without file:// prefix', function () {
    // Arrange
    File::shouldReceive('exists')
        ->with('https://github.com/user/repo/.llm.md')
        ->andReturn(false);

    $app = Application::factory()->create([
        'name' => 'GitHub App',
        'repo' => 'https://github.com/user/repo',
        'is_automated' => true,
        'overview' => null,
    ]);

    // Act
    artisan('reqqy:get-application-info', ['--app-id' => $app->id])
        ->assertSuccessful();

    // Assert - GitHub overview returns empty string (not implemented yet)
    expect($app->fresh()->overview)->toBe('');
});

it('strips file:// prefix and trailing slashes from local repo paths', function () {
    // Arrange
    File::shouldReceive('exists')
        ->with('/path/to/repo/.llm.md')
        ->andReturn(true);

    File::shouldReceive('get')
        ->with('/path/to/repo/.llm.md')
        ->andReturn('# Overview Content');

    $app = Application::factory()->create([
        'name' => 'Test App',
        'repo' => 'file:///path/to/repo/',
        'is_automated' => true,
        'overview' => null,
    ]);

    // Act
    artisan('reqqy:get-application-info', ['--app-id' => $app->id])
        ->assertSuccessful();

    // Assert
    expect($app->fresh()->overview)->toBe('# Overview Content');
});
