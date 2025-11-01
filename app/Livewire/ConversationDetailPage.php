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

    public array $expandedDocuments = [];

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

    public function toggleDocument(int $documentId): void
    {
        if (in_array($documentId, $this->expandedDocuments)) {
            $this->expandedDocuments = array_diff($this->expandedDocuments, [$documentId]);
        } else {
            $this->expandedDocuments[] = $documentId;
        }
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
