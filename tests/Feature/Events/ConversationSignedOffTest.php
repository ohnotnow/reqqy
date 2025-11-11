<?php

use App\Events\ConversationSignedOff;
use App\Jobs\GenerateFeatureRequestPrdJob;
use App\Jobs\GenerateNewApplicationPrdJob;
use App\Jobs\ResearchAlternativesJob;
use App\Jobs\TechnicalAssessmentJob;
use App\Models\Application;
use App\Models\Conversation;
use Illuminate\Support\Facades\Bus;

test('it dispatches batch with research and PRD jobs for new application conversation', function () {
    Bus::fake();

    $conversation = Conversation::factory()->create([
        'application_id' => null,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Bus::assertBatched(function ($batch) use ($conversation) {
        return $batch->name === "[New App] Research & PRD: Conv {$conversation->id}"
            && count($batch->jobs) === 2
            && collect($batch->jobs)->contains(fn ($job) => $job instanceof ResearchAlternativesJob && $job->conversation->id === $conversation->id)
            && collect($batch->jobs)->contains(fn ($job) => $job instanceof GenerateNewApplicationPrdJob && $job->conversation->id === $conversation->id);
    });
});

test('it dispatches chain with assessment then PRD for feature request with repo', function () {
    Bus::fake();

    $application = Application::factory()->create([
        'repo' => 'https://github.com/owner/repo',
    ]);

    $conversation = Conversation::factory()->create([
        'application_id' => $application->id,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Bus::assertChained([
        TechnicalAssessmentJob::class,
        GenerateFeatureRequestPrdJob::class,
    ]);
});

test('it dispatches batch with single PRD job for feature request without repo', function () {
    Bus::fake();

    $application = Application::factory()->create([
        'repo' => '',
    ]);

    $conversation = Conversation::factory()->create([
        'application_id' => $application->id,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Bus::assertBatched(function ($batch) use ($conversation) {
        return $batch->name === "[Feature] PRD Only: Conv {$conversation->id}"
            && count($batch->jobs) === 1
            && collect($batch->jobs)->contains(fn ($job) => $job instanceof GenerateFeatureRequestPrdJob && $job->conversation->id === $conversation->id);
    });
});

test('it dispatches chain for feature request with file protocol repo', function () {
    Bus::fake();

    $application = Application::factory()->create([
        'repo' => 'file:///path/to/local/repo',
    ]);

    $conversation = Conversation::factory()->create([
        'application_id' => $application->id,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Bus::assertChained([
        TechnicalAssessmentJob::class,
        GenerateFeatureRequestPrdJob::class,
    ]);
});

test('it uses correct queue assignments for all jobs', function () {
    Bus::fake();

    $conversation = Conversation::factory()->create([
        'application_id' => null,
        'signed_off_at' => now(),
    ]);

    ConversationSignedOff::dispatch($conversation);

    Bus::assertBatched(function ($batch) {
        return collect($batch->jobs)->every(function ($job) {
            if ($job instanceof ResearchAlternativesJob) {
                return $job->queue === 'long';
            }
            if ($job instanceof GenerateNewApplicationPrdJob) {
                return $job->queue === 'medium';
            }

            return true;
        });
    });
});
