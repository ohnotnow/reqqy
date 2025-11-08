<?php

namespace App\Listeners;

use App\Events\ConversationSignedOff;
use App\Jobs\ResearchAlternativesJob;

class DispatchResearchAlternatives
{
    public function handle(ConversationSignedOff $event): void
    {
        if ($event->conversation->application_id !== null) {
            return;
        }

        ResearchAlternativesJob::dispatch($event->conversation);
    }
}
