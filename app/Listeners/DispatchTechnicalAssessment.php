<?php

namespace App\Listeners;

use App\Jobs\TechnicalAssessmentJob;
use App\Events\ConversationSignedOff;
use App\Jobs\GenerateFeatureRequestPrdJob;

class DispatchTechnicalAssessment
{
    public function handle(ConversationSignedOff $event): void
    {
        if ($event->conversation->application_id === null) {
            return;
        }

        if ($event->conversation->application?->repo === null) {
            GenerateFeatureRequestPrdJob::dispatch($event->conversation);
            return;
        }

        TechnicalAssessmentJob::dispatch($event->conversation);
    }
}
