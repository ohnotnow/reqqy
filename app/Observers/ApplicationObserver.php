<?php

namespace App\Observers;

use App\Jobs\GetApplicationInfoJob;
use App\Models\Application;

class ApplicationObserver
{
    /**
     * Handle the Application "created" event.
     */
    public function created(Application $application): void
    {
        if ($application->is_automated) {
            GetApplicationInfoJob::dispatch($application->id);
        }
    }

    /**
     * Handle the Application "updated" event.
     */
    public function updated(Application $application): void
    {
        // Check if is_automated was changed to true
        if ($application->is_automated && $application->wasChanged('is_automated')) {
            GetApplicationInfoJob::dispatch($application->id);
        }
    }
}
