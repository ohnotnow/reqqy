<?php

namespace App\Jobs;

use App\Models\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GetApplicationInfoJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $appId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('reqqy:get-application-info', [
            'appId' => $this->appId,
        ]);
    }
}
