<?php

namespace App\Listeners;

use App\Events\TechnicalAssessmentCompleted;
use App\Jobs\GenerateFeatureRequestPrdJob;

class GenerateFeatureRequestPrd
{
    public function handle(TechnicalAssessmentCompleted $event): void
    {
        GenerateFeatureRequestPrdJob::dispatch($event->document->conversation);
    }
}
