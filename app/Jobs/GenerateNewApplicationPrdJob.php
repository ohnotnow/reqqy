<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

class GenerateNewApplicationPrdJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Conversation $conversation
    ) {}

    public function handle(): void
    {
        $messages = $this->conversation->messages()
            ->orderBy('created_at')
            ->get();

        $prompt = view('prompts.new-application-prd', [
            'messages' => $messages,
        ])->render();

        $response = Prism::text()
            ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
            ->withPrompt($prompt)
            ->withMaxTokens(4096)
            ->asText();

        Document::create([
            'conversation_id' => $this->conversation->id,
            'name' => 'Product Requirements Document',
            'content' => $response->text,
        ]);
    }
}
