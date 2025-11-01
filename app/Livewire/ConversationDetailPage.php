<?php

namespace App\Livewire;

use App\ConversationStatus;
use App\Models\Conversation;
use Livewire\Attributes\Url;
use Livewire\Component;

class ConversationDetailPage extends Component
{
    #[Url]
    public int $conversation_id;

    public string $status = '';

    public bool $showFullConversation = false;

    public function mount(): void
    {
        $conversation = Conversation::findOrFail($this->conversation_id);

        $this->authorize('view', $conversation);

        $this->status = $conversation->status->value;
    }

    public function updateStatus(): void
    {
        $conversation = Conversation::findOrFail($this->conversation_id);

        $this->authorize('update', $conversation);

        $validated = $this->validate([
            'status' => 'required|string',
        ]);

        $conversation->update(['status' => ConversationStatus::from($validated['status'])]);

        $this->status = $conversation->fresh()->status->value;
    }

    public function downloadDocument(int $documentId)
    {
        $conversation = Conversation::findOrFail($this->conversation_id);

        $this->authorize('view', $conversation);

        $document = $conversation->documents()->findOrFail($documentId);

        $filename = str($document->name)->slug()->append('.md')->toString();

        return response()->streamDownload(function () use ($document) {
            echo $document->content;
        }, $filename, [
            'Content-Type' => 'text/markdown',
        ]);
    }

    public function render()
    {
        $conversation = Conversation::with(['user', 'application', 'messages', 'documents'])
            ->findOrFail($this->conversation_id);

        return view('livewire.conversation-detail-page', [
            'conversation' => $conversation,
            'statuses' => ConversationStatus::cases(),
        ]);
    }
}
