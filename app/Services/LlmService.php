<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class LlmService
{
    /**
     * Generate a response based on conversation history.
     *
     * @param  Collection<int, \App\Models\Message>  $messages
     */
    public function generateResponse(Collection $messages): string
    {
        $prismMessages = $this->convertToPrismMessages($messages);

        $response = Prism::text()
            ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
            ->withMessages($prismMessages)
            ->asText();

        return $response->text;
    }

    /**
     * Convert application messages to Prism message format.
     *
     * @param  Collection<int, \App\Models\Message>  $messages
     * @return array<int, UserMessage|AssistantMessage>
     */
    protected function convertToPrismMessages(Collection $messages): array
    {
        return $messages->map(function ($message) {
            if ($message->isFromUser()) {
                return new UserMessage($message->content);
            }

            return new AssistantMessage($message->content);
        })->toArray();
    }
}
