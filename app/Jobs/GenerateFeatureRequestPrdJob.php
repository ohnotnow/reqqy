<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\Document;
use App\Services\LlmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateFeatureRequestPrdJob implements ShouldQueue
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

        $systemPrompt = view('prompts.feature-request-prd', [
            'conversation' => $this->conversation,
        ])->render();

        $content = $llmService->generateResponse(
            conversation: $this->conversation,
            messages: $messages,
            systemPrompt: $systemPrompt
        );

        Document::create([
            'conversation_id' => $this->conversation->id,
            'name' => 'Feature Request Document',
            'content' => $content,
        ]);
    }
}
