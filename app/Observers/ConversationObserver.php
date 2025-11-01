<?php

namespace App\Observers;

use App\ApplicationCategory;
use App\ConversationStatus;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\NewProposedApplicationCreated;
use Illuminate\Support\Str;

class ConversationObserver
{
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
        $firstMessage = $conversation->messages()
            ->whereNotNull('user_id')
            ->orderBy('created_at')
            ->first();

        if ($firstMessage) {
            return Str::limit($firstMessage->content, 50, '');
        }

        return 'New Application Proposal';
    }

    private function notifyAdmins(Application $application): void
    {
        $admins = User::where('is_admin', true)->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewProposedApplicationCreated($application));
        }
    }
}
