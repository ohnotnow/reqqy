<?php

namespace App\Listeners;

use App\Events\ConversationSignedOff;
use App\Jobs\TechnicalAssessmentJob;

class DispatchTechnicalAssessment
{
    public function handle(ConversationSignedOff $event): void
    {
        if ($event->conversation->application_id === null) {
            return;
        }

        if ($event->conversation->application?->repo === null) {
            return;
        }

        TechnicalAssessmentJob::dispatch($event->conversation);
    }
}
