<?php

use App\Events\ConversationSignedOff;
use App\Jobs\GenerateFeatureRequestPrdJob;
use App\Jobs\GenerateNewApplicationPrdJob;
use App\Jobs\ResearchAlternativesJob;
use App\Jobs\TechnicalAssessmentJob;
use App\Models\Application;
use App\Models\Conversation;
use Illuminate\Support\Facades\Queue;

test('it dispatches research and PRD jobs for new application conversation', function () {
    Queue::fake();

    $conversation = Conversation::factory()->create([
        'application_id' => null,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Queue::assertPushed(ResearchAlternativesJob::class, function ($job) use ($conversation) {
        return $job->conversation->id === $conversation->id;
    });

    Queue::assertPushed(GenerateNewApplicationPrdJob::class, function ($job) use ($conversation) {
        return $job->conversation->id === $conversation->id;
    });

    Queue::assertNotPushed(TechnicalAssessmentJob::class);
    Queue::assertNotPushed(GenerateFeatureRequestPrdJob::class);
});

test('it dispatches technical assessment job for feature request with repo', function () {
    Queue::fake();

    $application = Application::factory()->create([
        'repo' => 'https://github.com/owner/repo',
    ]);

    $conversation = Conversation::factory()->create([
        'application_id' => $application->id,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Queue::assertPushed(TechnicalAssessmentJob::class, function ($job) use ($conversation) {
        return $job->conversation->id === $conversation->id;
    });

    Queue::assertNotPushed(ResearchAlternativesJob::class);
    Queue::assertNotPushed(GenerateNewApplicationPrdJob::class);
    Queue::assertNotPushed(GenerateFeatureRequestPrdJob::class);
});

test('it dispatches PRD job directly for feature request without repo', function () {
    Queue::fake();

    $application = Application::factory()->create([
        'repo' => '',
    ]);

    $conversation = Conversation::factory()->create([
        'application_id' => $application->id,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Queue::assertPushed(GenerateFeatureRequestPrdJob::class);
    Queue::assertPushed(GenerateFeatureRequestPrdJob::class, function ($job) use ($conversation) {
        return $job->conversation->id === $conversation->id;
    });

    Queue::assertNotPushed(TechnicalAssessmentJob::class);
    Queue::assertNotPushed(ResearchAlternativesJob::class);
    Queue::assertNotPushed(GenerateNewApplicationPrdJob::class);
});

test('it does not dispatch technical assessment for feature request with file protocol repo', function () {
    Queue::fake();

    $application = Application::factory()->create([
        'repo' => 'file:///path/to/local/repo',
    ]);

    $conversation = Conversation::factory()->create([
        'application_id' => $application->id,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Queue::assertPushed(TechnicalAssessmentJob::class, function ($job) use ($conversation) {
        return $job->conversation->id === $conversation->id;
    });
});

test('it follows correct flow when technical assessment completes', function () {
    Queue::fake();

    $application = Application::factory()->create([
        'repo' => 'https://github.com/owner/repo',
    ]);

    $conversation = Conversation::factory()->create([
        'application_id' => $application->id,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Queue::assertPushed(TechnicalAssessmentJob::class);
    Queue::assertNotPushed(GenerateFeatureRequestPrdJob::class);
});
