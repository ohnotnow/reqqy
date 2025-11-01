<?php

namespace App\Livewire;

use App\Models\Conversation;
use Livewire\Component;

class ConversationsAdminPage extends Component
{
    public function render()
    {
        $this->authorize('viewAny', Conversation::class);

        return view('livewire.conversations-admin-page', [
            'conversations' => Conversation::with(['user', 'application', 'messages', 'documents'])
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
