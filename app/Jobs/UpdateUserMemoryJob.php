<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\UserMemory;
use App\Services\LlmService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\View;

class UpdateUserMemoryJob implements ShouldQueue
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

        $user = $this->conversation->user;
        $existingMemory = $user->memory;

        $this->conversation->load('application');

        $prompt = View::make('prompts.update-user-memory-gpt', [
            'user' => $user,
            'conversation' => $this->conversation,
            'messages' => $messages,
            'existingMemory' => $existingMemory?->memory_content,
        ])->render();

        $updatedMemory = $llmService->generateResponse(
            conversation: $this->conversation,
            messages: collect(),
            systemPrompt: $prompt,
            useSmallModel: true
        );

        $cleanedMemory = trim($updatedMemory);

        UserMemory::updateOrCreate(
            ['user_id' => $user->id],
            ['memory_content' => $cleanedMemory]
        );
    }
}
