<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\LlmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class GenerateConversationTitleJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Conversation $conversation
    ) {}

    public function handle(LlmService $llmService): void
    {
        $messages = $this->conversation->messages()
            ->orderBy('created_at')
            ->get();

        if ($messages->isEmpty()) {
            return;
        }

        $this->conversation->load('application');

        $prompt = View::make('prompts.generate-conversation-title', [
            'conversation' => $this->conversation,
            'messages' => $messages,
        ])->render();

        try {
            $title = $llmService->generateResponse(
                conversation: $this->conversation,
                messages: collect(),
                systemPrompt: $prompt,
                useSmallModel: true
            );

            $cleanedTitle = Str::limit(trim($title), 100, '');

            $this->conversation->update([
                'title' => $cleanedTitle,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate conversation title', [
                'conversation_id' => $this->conversation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
