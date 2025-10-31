<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDocumentCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Document $document
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $conversation = $this->document->conversation;
        $requestType = $conversation->application_id ? 'Feature Request' : 'New Application';

        return (new MailMessage)
            ->subject("New PRD Generated: {$requestType}")
            ->greeting('Hello Reqqy Admin!')
            ->line("A new Product Requirements Document has been generated for a {$requestType}.")
            ->action('View Conversation', route('conversation', ['conversation_id' => $conversation->id]))
            ->line('Review the conversation and PRD to understand the requirements captured.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'conversation_id' => $this->document->conversation_id,
        ];
    }
}
