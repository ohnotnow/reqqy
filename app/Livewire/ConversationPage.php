<?php

namespace App\Livewire;

use App\Models\Conversation;
use Livewire\Attributes\Url;
use Livewire\Component;

class ConversationPage extends Component
{
    #[Url]
    public ?int $conversation_id = null;

    #[Url]
    public ?int $application_id = null;

    public ?Conversation $conversation = null;

    public function mount()
    {
        if ($this->conversation_id) {
            $this->conversation = auth()->user()->conversations()->findOrFail($this->conversation_id);
        } else {
            $this->conversation = Conversation::create([
                'user_id' => auth()->id(),
                'application_id' => $this->application_id,
            ]);
            
            $this->conversation_id = $this->conversation->id;
        }
    }

    public function render()
    {
        return view('livewire.conversation-page');
    }
}

