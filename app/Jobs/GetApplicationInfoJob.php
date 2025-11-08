<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class GetApplicationInfoJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $appId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('reqqy:get-application-info', [
            '--app-id' => $this->appId,
        ]);
    }
}
