<?php

namespace App\Observers;

use App\ApplicationCategory;
use App\ConversationStatus;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\NewProposedApplicationCreated;
use App\Services\LlmService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class ConversationObserver
{
    public function __construct(
        protected LlmService $llmService
    ) {}
    /**
     * Handle the Conversation "updated" event.
     */
    public function updated(Conversation $conversation): void
    {
        if (!$this->shouldCreateProposedApplication($conversation)) {
            return;
        }

        $application = Application::create([
            'category' => ApplicationCategory::Proposed,
            'source_conversation_id' => $conversation->id,
            'name' => $this->extractApplicationName($conversation),
        ]);

        $conversation->application_id = $application->id;
        $conversation->saveQuietly();

        $this->notifyAdmins($application);
    }

    private function shouldCreateProposedApplication(Conversation $conversation): bool
    {
        if (!$conversation->wasChanged('status')) {
            return false;
        }

        if ($conversation->status !== ConversationStatus::Approved) {
            return false;
        }

        if ($conversation->application_id !== null) {
            return false;
        }

        return true;
    }

    private function extractApplicationName(Conversation $conversation): string
    {
        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        if ($messages->isEmpty()) {
            return 'New Application Proposal';
        }

        try {
            $prompt = View::make('prompts.extract-application-name', [
                'messages' => $messages,
            ])->render();

            $name = $this->llmService->generateResponse(
                conversation: $conversation,
                messages: collect(),
                systemPrompt: $prompt,
                useSmallModel: true
            );

            return Str::limit(trim($name), 100, '');
        } catch (\Exception $e) {
            $firstMessage = $messages->firstWhere('user_id', '!=', null);

            if ($firstMessage) {
                return Str::limit($firstMessage->content, 50, '');
            }

            return 'New Application Proposal';
        }
    }

    private function notifyAdmins(Application $application): void
    {
        $admins = User::where('is_admin', true)->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewProposedApplicationCreated($application));
        }
    }
}
