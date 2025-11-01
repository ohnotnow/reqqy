<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\LlmService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    /** @var Collection<int, array<string, mixed>> */
    public Collection $conversationMessages;

    public string $debugSummary = '';

    /** @var array<int, string> */
    public array $debugMessages = [];

    protected ?string $pendingMessageKey = null;

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

        $this->refreshMessages('Mounted conversation');
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

        $this->refreshMessages('User message persisted');

        $this->messageContent = '';

        $this->isAwaitingResponse = true;
        $this->addPendingMessage();
        $this->updateDebugInfo('Awaiting LLM response');

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
        $this->updateDebugInfo('Generating LLM response from event');

        $this->generateLlmResponse();

        $this->refreshMessages('LLM response generated via event');
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
            $this->updateDebugInfo('Polling triggered LLM response');

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

        $this->refreshMessages('Conversation signed off');

        $this->conversation->refresh();

        $this->isAwaitingResponse = false;
        $this->updateDebugInfo('Conversation signed off');
    }

    protected function generateLlmResponse(): void
    {
        $this->updateDebugInfo('Starting LLM response generation');

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

        $this->pendingMessageKey = null;

        $this->refreshMessages('LLM response persisted');

        $this->isAwaitingResponse = false;
        $this->updateDebugInfo('Awaiting flag reset after LLM response');
    }

    protected function loadMessages(): Collection
    {
        return Message::query()
            ->where('conversation_id', $this->conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message) => $this->normalizeMessage($message));
    }

    protected function refreshMessages(string $context): void
    {
        $this->conversationMessages = $this->loadMessages();

        if ($this->isAwaitingResponse) {
            $this->ensurePendingMessageExists();
        }

        $this->updateDebugInfo($context);
    }

    protected function updateDebugInfo(string $context): void
    {
        $latest = $this->conversationMessages->last();
        $lastAuthor = 'none';

        if ($latest) {
            if ($latest['is_from_user']) {
                $lastAuthor = 'user';
            } elseif ($latest['is_pending']) {
                $lastAuthor = 'reqqy-pending';
            } else {
                $lastAuthor = 'reqqy';
            }
        }

        $this->debugSummary = sprintf(
            '%s | messages:%d | awaiting:%s | last:%s',
            $context,
            $this->conversationMessages->count(),
            $this->isAwaitingResponse ? 'yes' : 'no',
            $lastAuthor
        );

        $this->recordDebug($this->debugSummary);
    }

    protected function recordDebug(string $message): void
    {
        $timestamped = now()->format('H:i:s').' '.$message;
        $this->debugMessages[] = $timestamped;

        if (count($this->debugMessages) > 25) {
            $this->debugMessages = array_slice($this->debugMessages, -25);
        }

        Log::debug('[ConversationPage] '.$message);
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
            $this->pendingMessageKey = 'pending-'.Str::uuid()->toString();
        }

        if ($this->conversationMessages->contains(fn ($message) => $message['id'] === $this->pendingMessageKey)) {
            return;
        }

        $this->conversationMessages->push($this->createPendingMessage());

        $this->updateDebugInfo('Pending message added');
    }

    protected function ensurePendingMessageExists(): void
    {
        if (! $this->isAwaitingResponse) {
            return;
        }

        if (! $this->pendingMessageKey) {
            $this->pendingMessageKey = 'pending-'.Str::uuid()->toString();
        }

        if ($this->conversationMessages->contains(fn ($message) => $message['id'] === $this->pendingMessageKey)) {
            return;
        }

        $this->conversationMessages->push($this->createPendingMessage());
    }

    protected function createPendingMessage(): array
    {
        return [
            'id' => $this->pendingMessageKey ?? 'pending-'.Str::uuid()->toString(),
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
