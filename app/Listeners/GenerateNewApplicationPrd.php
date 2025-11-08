<?php

namespace App\Listeners;

use App\Events\ConversationSignedOff;
use App\Jobs\GenerateNewApplicationPrdJob;

class GenerateNewApplicationPrd
{
    public function handle(ConversationSignedOff $event): void
    {
        if ($event->conversation->application_id !== null) {
            return;
        }

        GenerateNewApplicationPrdJob::dispatch($event->conversation);
    }
}
