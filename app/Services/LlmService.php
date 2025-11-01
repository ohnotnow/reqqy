<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
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
     * @param  string|null  $systemPrompt  Custom system prompt, or null to use default chat prompt
     */
    public function generateResponse(Conversation $conversation, Collection $messages, ?string $systemPrompt = null): string
    {
        [$provider, $model] = $this->parseProviderAndModel();

        $systemPrompt = $systemPrompt ?? $this->renderChatPrompt($conversation);
        $prismMessages = $this->convertToPrismMessages($messages);

        $response = Prism::text()
            ->using($provider, $model)
            ->withSystemPrompt($systemPrompt)
            ->withMessages($prismMessages)
            ->asText();

        return $response->text;
    }

    /**
     * Render the default chat system prompt using the chat Blade template.
     */
    protected function renderChatPrompt(Conversation $conversation): string
    {
        return View::make('prompts.chat2', [
            'conversation' => $conversation->load('application'),
        ])->render();
    }

    /**
     * Parse the provider and model from the config using litellm format.
     *
     * @return array{Provider, string}
     *
     * @throws InvalidArgumentException
     */
    protected function parseProviderAndModel(): array
    {
        $llmConfig = config('reqqy.llm');

        if (empty($llmConfig)) {
            throw new InvalidArgumentException('LLM configuration is not set. Please set REQQY_LLM in your .env file.');
        }

        if (! str_contains($llmConfig, '/')) {
            throw new InvalidArgumentException('LLM configuration must be in the format "provider/model" (e.g., "anthropic/claude-3-5-sonnet").');
        }

        [$providerName, $model] = explode('/', $llmConfig, 2);

        $provider = match (strtolower($providerName)) {
            'anthropic' => Provider::Anthropic,
            'openai' => Provider::OpenAI,
            'openrouter' => Provider::OpenRouter,
            default => throw new InvalidArgumentException("Unsupported provider: {$providerName}. Supported providers are: anthropic, openai, openrouter."),
        };

        return [$provider, $model];
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
