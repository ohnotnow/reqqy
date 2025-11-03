<?php

namespace App\Livewire;

use App\Jobs\ResearchAlternativesJob;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\LlmService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ConversationPage extends Component
{
    public const MESSAGE_THRESHOLD_FOR_TITLE = 4;

    #[Url]
    public ?int $conversation_id = null;

    #[Url]
    public ?int $application_id = null;

    public ?Conversation $conversation = null;

    public string $messageContent = '';

    public bool $isAwaitingResponse = false;

    /** @var Collection<int, array<string, mixed>> */
    public Collection $conversationMessages;

    protected ?string $pendingMessageKey = null;

    public function mount(): void
    {
        if ($this->conversation_id) {
            $this->conversation = Conversation::findOrFail($this->conversation_id);
            $this->authorize('view', $this->conversation);
        } else {
            $this->conversation = Conversation::create([
                'user_id' => auth()->id(),
                'application_id' => $this->application_id,
            ]);

            $this->redirect(route('conversation', ['conversation_id' => $this->conversation->id]), navigate: true);
        }

        $this->refreshMessages();
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

        $this->refreshMessages();

        $this->messageContent = '';

        if ($this->conversation->messages()->count() >= self::MESSAGE_THRESHOLD_FOR_TITLE
            && $this->conversation->title === 'New conversation') {
            \App\Jobs\GenerateConversationTitleJob::dispatch($this->conversation);
        }

        $this->isAwaitingResponse = true;
        $this->addPendingMessage();
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

        $this->ensurePendingMessageExists();
        $this->generateLlmResponse();

        $this->refreshMessages();
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
            $this->addPendingMessage();

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

        if (! $this->conversation->application_id) {
            // New application request
            ResearchAlternativesJob::dispatch($this->conversation);
            \App\Jobs\GenerateNewApplicationPrdJob::dispatch($this->conversation);
        } else {
            // Feature request for existing application
            \App\Jobs\GenerateFeatureRequestPrdJob::dispatch($this->conversation);
        }

        sleep(1);

        Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => null,
            'content' => "Thank you for providing your requirements! I'll now generate the documentation for the development team. An admin will review your request and be in touch soon.",
        ]);

        $this->refreshMessages();

        $this->conversation->refresh();

        $this->isAwaitingResponse = false;
    }

    protected function generateLlmResponse(): void
    {
        $llmService = app(LlmService::class);
        $messages = $this->conversation->messages()->orderBy('created_at')->get();
        $responseText = $llmService->generateResponse($this->conversation, $messages);

        Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => null,
            'content' => $responseText,
        ]);

        $this->pendingMessageKey = null;

        $this->refreshMessages();

        $this->isAwaitingResponse = false;
    }

    protected function loadMessages(): Collection
    {
        return Message::query()
            ->where('conversation_id', $this->conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message) => $this->normalizeMessage($message));
    }

    protected function refreshMessages(): void
    {
        $this->conversationMessages = $this->loadMessages();

        if ($this->isAwaitingResponse) {
            $this->ensurePendingMessageExists();
        }
    }

    protected function normalizeMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'content' => $message->content,
            'is_from_user' => $message->isFromUser(),
            'is_pending' => false,
            'created_at' => $message->created_at?->toDateTimeString(),
        ];
    }

    protected function addPendingMessage(): void
    {
        if (! $this->isAwaitingResponse) {
            return;
        }

        if (! $this->pendingMessageKey) {
            $this->pendingMessageKey = 'pending-'.(string) str()->uuid();
        }

        if ($this->conversationMessages->contains(fn ($message) => $message['id'] === $this->pendingMessageKey)) {
            return;
        }

        $this->conversationMessages->push($this->createPendingMessage());
    }

    protected function ensurePendingMessageExists(): void
    {
        if (! $this->isAwaitingResponse) {
            return;
        }

        if (! $this->pendingMessageKey) {
            $this->pendingMessageKey = 'pending-'.(string) str()->uuid();
        }

        if ($this->conversationMessages->contains(fn ($message) => $message['id'] === $this->pendingMessageKey)) {
            return;
        }

        $this->conversationMessages->push($this->createPendingMessage());
    }

    protected function createPendingMessage(): array
    {
        return [
            'id' => $this->pendingMessageKey ?? 'pending-'.(string) str()->uuid(),
            'content' => 'Thinking through your request...',
            'is_from_user' => false,
            'is_pending' => true,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public function render()
    {
        return view('livewire.conversation-page');
    }
}
