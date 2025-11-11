<?php

namespace App\Listeners;

use App\Events\ConversationSignedOff;
use App\Jobs\GenerateFeatureRequestPrdJob;
use App\Jobs\GenerateNewApplicationPrdJob;
use App\Jobs\ResearchAlternativesJob;
use App\Jobs\TechnicalAssessmentJob;
use Illuminate\Support\Facades\Bus;

class OrchestrateConversationWorkflow
{
    public function handle(ConversationSignedOff $event): void
    {
        $conversation = $event->conversation;

        match (true) {
            // PATH 1: New Application - Research + PRD (parallel)
            $conversation->application_id === null => Bus::batch([
                new ResearchAlternativesJob($conversation),
                new GenerateNewApplicationPrdJob($conversation),
            ])->name("[New App] Research & PRD: Conv {$conversation->id}")->dispatch(),

            // PATH 2: Feature Request with repo - Assessment → PRD (sequential)
            $conversation->application?->repo && $conversation->application->repo !== '' => Bus::chain([
                new TechnicalAssessmentJob($conversation),
                new GenerateFeatureRequestPrdJob($conversation),
            ])->dispatch(),

            // PATH 3: Feature Request without repo - PRD only
            default => Bus::batch([
                new GenerateFeatureRequestPrdJob($conversation),
            ])->name("[Feature] PRD Only: Conv {$conversation->id}")->dispatch(),
        };
    }
}
