<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\LlmService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
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

    public bool $isAwaitingResponse = false;

    /** @var Collection<int, Message> */
    public Collection $conversationMessages;

    public function mount(): void
    {
        if ($this->conversation_id) {
            $this->conversation = auth()->user()->conversations()->findOrFail($this->conversation_id);
        } else {
            $this->conversation = Conversation::create([
                'user_id' => auth()->id(),
                'application_id' => $this->application_id,
            ]);

            $this->redirect(route('conversation', ['conversation_id' => $this->conversation->id]), navigate: true);
        }

        $this->conversationMessages = $this->loadMessages();
    }

    public function sendMessage(): void
    {
        if ($this->conversation->isSignedOff()) {
            return;
        }

        $validated = $this->validate([
            'messageContent' => ['required', 'string', 'max:10000'],
        ]);

        $message = Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => auth()->id(),
            'content' => $validated['messageContent'],
        ]);

        $this->conversationMessages = $this->loadMessages();

        $this->messageContent = '';

        $this->isAwaitingResponse = true;

        $this->dispatch(
            'user-message-created',
            messageId: $message->id
        );
    }

    #[On('user-message-created')]
    public function handleUserMessageCreated(int $messageId): void
    {
        if ($this->conversation->isSignedOff()) {
            return;
        }

        $message = Message::find($messageId);

        if (! $message || $message->conversation_id !== $this->conversation->id || ! $message->isFromUser()) {
            $this->isAwaitingResponse = false;

            return;
        }

        $this->generateLlmResponse();

        $this->conversationMessages = $this->loadMessages();
    }

    public function checkForUnansweredMessages(): void
    {
        if ($this->conversation->isSignedOff()) {
            return;
        }

        $lastMessage = Message::query()
            ->where('conversation_id', $this->conversation->id)
            ->latest()
            ->first();

        if ($lastMessage && $lastMessage->isFromUser()) {
            $this->isAwaitingResponse = true;

            $this->generateLlmResponse();
        }
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

        $this->conversationMessages = $this->loadMessages();

        $this->conversation->refresh();

        $this->isAwaitingResponse = false;
    }

    protected function generateLlmResponse(): void
    {
        if (! app()->runningUnitTests()) {
            sleep(5);
        }

        // Fake response for testing - remove this later!
        $responseText = 'Claude is the best';

        // $llmService = app(LlmService::class);
        // $messages = $this->conversation->messages()->orderBy('created_at')->get();
        // $responseText = $llmService->generateResponse($this->conversation, $messages);

        Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => null,
            'content' => $responseText,
        ]);

        $this->conversationMessages = $this->loadMessages();

        $this->isAwaitingResponse = false;
    }

    protected function loadMessages(): Collection
    {
        return Message::query()
            ->where('conversation_id', $this->conversation->id)
            ->orderBy('created_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.conversation-page');
    }
}
