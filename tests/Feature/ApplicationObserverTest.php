<?php

use App\Jobs\GetApplicationInfoJob;
use App\Models\Application;
use Illuminate\Support\Facades\Queue;

it('dispatches job when automated application is created', function () {
    // Arrange
    Queue::fake();

    // Act
    $app = Application::factory()->create([
        'name' => 'Test App',
        'is_automated' => true,
        'repo' => 'file:///path/to/repo',
    ]);

    // Assert
    Queue::assertPushed(GetApplicationInfoJob::class, function ($job) use ($app) {
        return $job->appId === $app->id;
    });
});

it('does not dispatch job when non-automated application is created', function () {
    // Arrange
    Queue::fake();

    // Act
    Application::factory()->create([
        'name' => 'Manual App',
        'is_automated' => false,
        'repo' => 'file:///path/to/repo',
    ]);

    // Assert
    Queue::assertNotPushed(GetApplicationInfoJob::class);
});

it('dispatches job when application is updated to automated', function () {
    // Arrange
    Queue::fake();

    $app = Application::factory()->create([
        'name' => 'Test App',
        'is_automated' => false,
        'repo' => 'file:///path/to/repo',
    ]);

    Queue::assertNotPushed(GetApplicationInfoJob::class);

    // Act
    $app->update(['is_automated' => true]);

    // Assert
    Queue::assertPushed(GetApplicationInfoJob::class, function ($job) use ($app) {
        return $job->appId === $app->id;
    });
});

it('does not dispatch job when automated application is updated but is_automated unchanged', function () {
    // Arrange
    Queue::fake();

    $app = Application::factory()->create([
        'name' => 'Test App',
        'is_automated' => true,
        'repo' => 'file:///path/to/repo',
    ]);

    // Clear the queue from the creation event
    Queue::fake();

    // Act - update other fields but not is_automated
    $app->update(['name' => 'Updated Name']);

    // Assert - no new job dispatched
    Queue::assertNotPushed(GetApplicationInfoJob::class);
});

it('does not dispatch job when application is updated from automated to non-automated', function () {
    // Arrange
    Queue::fake();

    $app = Application::factory()->create([
        'name' => 'Test App',
        'is_automated' => true,
        'repo' => 'file:///path/to/repo',
    ]);

    // Clear the queue from the creation event
    Queue::fake();

    // Act
    $app->update(['is_automated' => false]);

    // Assert - no job dispatched when changing to false
    Queue::assertNotPushed(GetApplicationInfoJob::class);
});
