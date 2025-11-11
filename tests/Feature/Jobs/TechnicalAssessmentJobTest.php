<?php

use App\DocumentType;
use App\Jobs\TechnicalAssessmentJob;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Document;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

test('it creates technical assessment document', function () {
    Event::fake();
    Queue::fake();

    $application = Application::factory()->create(['repo' => 'https://github.com/owner/repo']);
    $conversation = Conversation::factory()->create(['application_id' => $application->id]);

    $job = new TechnicalAssessmentJob($conversation);
    $job->handle();

    $document = Document::where('conversation_id', $conversation->id)
        ->where('type', DocumentType::TechnicalAssessment)
        ->first();

    expect($document)->not->toBeNull();
    expect($document->name)->toBe('Technical Assessment');
    expect($document->type)->toBe(DocumentType::TechnicalAssessment);
});

test('it creates valid JSON assessment content', function () {
    Event::fake();
    Queue::fake();

    $application = Application::factory()->create(['repo' => 'https://github.com/owner/repo']);
    $conversation = Conversation::factory()->create(['application_id' => $application->id]);

    $job = new TechnicalAssessmentJob($conversation);
    $job->handle();

    $document = Document::where('conversation_id', $conversation->id)
        ->where('type', DocumentType::TechnicalAssessment)
        ->first();

    $assessment = json_decode($document->content, true);

    expect($assessment)->toBeArray();
    expect($assessment)->toHaveKeys([
        'size_estimate',
        'confidence',
        'impacted_areas',
        'risks',
        'unknowns',
        'assumptions',
        'implementation_notes',
    ]);
});

test('it includes proper metadata', function () {
    Event::fake();
    Queue::fake();

    $application = Application::factory()->create(['repo' => 'https://github.com/owner/repo']);
    $conversation = Conversation::factory()->create(['application_id' => $application->id]);

    $job = new TechnicalAssessmentJob($conversation);
    $job->handle();

    $document = Document::where('conversation_id', $conversation->id)
        ->where('type', DocumentType::TechnicalAssessment)
        ->first();

    expect($document->metadata)->toBeArray();
    expect($document->metadata)->toHaveKeys([
        'model',
        'prompt_version',
        'generated_at',
        'application_id',
        'repo_path',
    ]);
    expect($document->metadata['application_id'])->toBe($application->id);
    expect($document->metadata['repo_path'])->toBe('https://github.com/owner/repo');
    expect($document->metadata['prompt_version'])->toBe('v1.0');
});

test('it throws exception when conversation has no application', function () {
    Event::fake();
    Queue::fake();

    $conversation = Conversation::factory()->create(['application_id' => null]);

    $job = new TechnicalAssessmentJob($conversation);

    expect(fn () => $job->handle())->toThrow(\Exception::class, 'Application repo is required for technical assessment');
});

test('it creates assessment with valid size estimate', function () {
    Event::fake();
    Queue::fake();

    $application = Application::factory()->create(['repo' => 'https://github.com/owner/repo']);
    $conversation = Conversation::factory()->create(['application_id' => $application->id]);

    $job = new TechnicalAssessmentJob($conversation);
    $job->handle();

    $document = Document::where('conversation_id', $conversation->id)
        ->where('type', DocumentType::TechnicalAssessment)
        ->first();

    $assessment = json_decode($document->content, true);

    expect($assessment['size_estimate'])->toBeIn(['S', 'M', 'L', 'XL']);
    expect($assessment['confidence'])->toBeGreaterThanOrEqual(0.0);
    expect($assessment['confidence'])->toBeLessThanOrEqual(1.0);
});
