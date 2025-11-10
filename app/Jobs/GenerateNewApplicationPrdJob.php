<?php

namespace App\Jobs;

use App\DocumentType;
use App\Models\Conversation;
use App\Models\Document;
use App\Services\LlmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateNewApplicationPrdJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Conversation $conversation
    ) {
        $this->onQueue('document-generation');
    }

    public function handle(LlmService $llmService): void
    {
        $messages = $this->conversation->messages()
            ->orderBy('created_at')
            ->get();

        $systemPrompt = view('prompts.new-application-prd', [
            'conversation' => $this->conversation,
        ])->render();

        $totalMessageChars = $messages->sum(fn ($msg) => strlen($msg->content));

        Log::info('Generating New Application PRD', [
            'conversation_id' => $this->conversation->id,
            'system_prompt_chars' => strlen($systemPrompt),
            'message_count' => $messages->count(),
            'total_message_chars' => $totalMessageChars,
            'total_input_chars' => strlen($systemPrompt) + $totalMessageChars,
        ]);

        $content = $llmService->generateResponse(
            conversation: $this->conversation,
            messages: $messages,
            systemPrompt: $systemPrompt,
            maxTokens: config('reqqy.max_tokens.prd', 100000)
        );

        Document::create([
            'conversation_id' => $this->conversation->id,
            'type' => DocumentType::Prd,
            'name' => 'Product Requirements Document',
            'content' => $content,
        ]);
    }
}
