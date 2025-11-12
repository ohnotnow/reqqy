<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\LlmService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\View;

class GenerateConversationSummaryJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Conversation $conversation
    ) {
        $this->onQueue('short');
    }

    public function handle(LlmService $llmService): void
    {
        $messages = $this->conversation->messages()
            ->orderBy('created_at')
            ->get();

        if ($messages->isEmpty()) {
            return;
        }

        $this->conversation->load('application');

        $prompt = View::make('prompts.generate-conversation-summary', [
            'conversation' => $this->conversation,
            'messages' => $messages,
        ])->render();

        $summary = $llmService->generateResponse(
            conversation: $this->conversation,
            messages: collect(),
            systemPrompt: $prompt,
            useSmallModel: true
        );

        $cleanedSummary = trim($summary);

        $this->conversation->update([
            'summary' => $cleanedSummary,
        ]);
    }
}
