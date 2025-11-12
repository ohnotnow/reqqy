<?php

namespace App\Listeners;

use App\Events\ConversationSignedOff;
use App\Jobs\GenerateConversationSummaryJob;
use App\Jobs\GenerateFeatureRequestPrdJob;
use App\Jobs\GenerateNewApplicationPrdJob;
use App\Jobs\ResearchAlternativesJob;
use App\Jobs\TechnicalAssessmentJob;
use App\Jobs\UpdateUserMemoryJob;
use Illuminate\Support\Facades\Bus;

class OrchestrateConversationWorkflow
{
    public function handle(ConversationSignedOff $event): void
    {
        $conversation = $event->conversation;

        match (true) {
            // PATH 1: New Application - Research + PRD + Summary + Memory (parallel)
            $conversation->application_id === null => Bus::batch([
                new ResearchAlternativesJob($conversation),
                new GenerateNewApplicationPrdJob($conversation),
                new GenerateConversationSummaryJob($conversation),
                new UpdateUserMemoryJob($conversation),
            ])->name("[New App] Research & PRD & Summary & Memory: Conv {$conversation->id}")->dispatch(),

            // PATH 2: Feature Request with repo - Assessment → PRD, Summary, Memory (parallel after assessment)
            $conversation->application?->repo && $conversation->application->repo !== '' => Bus::chain([
                new TechnicalAssessmentJob($conversation),
                Bus::batch([
                    new GenerateFeatureRequestPrdJob($conversation),
                    new GenerateConversationSummaryJob($conversation),
                    new UpdateUserMemoryJob($conversation),
                ]),
            ])->dispatch(),

            // PATH 3: Feature Request without repo - PRD + Summary + Memory (parallel)
            default => Bus::batch([
                new GenerateFeatureRequestPrdJob($conversation),
                new GenerateConversationSummaryJob($conversation),
                new UpdateUserMemoryJob($conversation),
            ])->name("[Feature] PRD & Summary & Memory: Conv {$conversation->id}")->dispatch(),
        };
    }
}
