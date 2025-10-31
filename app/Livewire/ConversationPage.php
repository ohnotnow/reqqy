<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use Livewire\Attributes\Url;
use Livewire\Component;

class ConversationPage extends Component
{
    #[Url]
    public ?int $conversation_id = null;

    #[Url]
    public ?int $application_id = null;

    public ?Conversation $conversation = null;

    public string $messageContent = '';

    public function mount(): void
    {
        if ($this->conversation_id) {
            $this->conversation = auth()->user()->conversations()->with('messages')->findOrFail($this->conversation_id);
        } else {
            $this->conversation = Conversation::create([
                'user_id' => auth()->id(),
                'application_id' => $this->application_id,
            ]);

            $this->redirect(route('conversation', ['conversation_id' => $this->conversation->id]), navigate: true);
        }
    }

    public function sendMessage(): void
    {
        if ($this->conversation->isSignedOff()) {
            return;
        }

        $validated = $this->validate([
            'messageContent' => ['required', 'string', 'max:10000'],
        ]);

        Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => auth()->id(),
            'content' => $validated['messageContent'],
        ]);

        $this->messageContent = '';

        $this->generateLlmResponse();

        $this->conversation->load('messages');
    }

    public function signOff(): void
    {
        if ($this->conversation->isSignedOff()) {
            return;
        }

        $this->conversation->update([
            'signed_off_at' => now(),
        ]);

        sleep(1);

        Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => null,
            'content' => "Thank you for providing your requirements! I'll now generate the documentation for the development team. An admin will review your request and be in touch soon.",
        ]);

        $this->conversation->load('messages');
    }

    protected function generateLlmResponse(): void
    {
        sleep(1);

        Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => null,
            'content' => 'Claude is the best',
        ]);
    }

    public function render()
    {
        return view('livewire.conversation-page');
    }
}
