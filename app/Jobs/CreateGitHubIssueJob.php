<?php

namespace App\Jobs;

use App\Models\Conversation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CreateGitHubIssueJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Conversation $conversation)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $application = $this->conversation->application;

        Log::info('CreateGitHubIssueJob would create a GitHub issue', [
            'conversation_id' => $this->conversation->id,
            'application_name' => $application->name,
            'repo' => $application->repo,
            'github_token_configured' => ! empty(config('reqqy.api_keys.github')),
        ]);
    }
}
