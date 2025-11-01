<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewProposedApplicationCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Application $application
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Application Proposed: {$this->application->name}")
            ->greeting('Hello Reqqy Admin!')
            ->line("A new application proposal has been created: {$this->application->name}")
            ->line('This proposal was automatically created from an approved conversation.')
            ->action('View Conversation', route('admin.conversations.show', ['conversation_id' => $this->application->source_conversation_id]))
            ->line('Review the conversation and consider promoting this proposal to an Internal application in Settings.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'conversation_id' => $this->application->source_conversation_id,
        ];
    }
}
