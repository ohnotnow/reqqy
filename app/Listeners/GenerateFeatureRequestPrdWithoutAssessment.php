<?php

namespace App\Listeners;

use App\Events\ConversationSignedOff;
use App\Jobs\GenerateFeatureRequestPrdJob;

class GenerateFeatureRequestPrdWithoutAssessment
{
    public function handle(ConversationSignedOff $event): void
    {
        if ($event->conversation->application_id === null) {
            return;
        }

        if ($event->conversation->application?->repo !== null) {
            return;
        }

        GenerateFeatureRequestPrdJob::dispatch($event->conversation);
    }
}
