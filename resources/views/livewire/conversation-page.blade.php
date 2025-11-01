<div class="h-screen flex flex-col" wire:poll.50s>
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div>
                <flux:heading size="lg">
                    @if($conversation->application)
                        {{ $conversation->application->name }} - New Feature
                    @else
                        New Application Request
                    @endif
                </flux:heading>
                <flux:subheading class="mt-1">
                    Describe your requirements and we'll help capture them
                </flux:subheading>
            </div>
            @if(!$conversation->isSignedOff())
                <flux:button wire:click="signOff" variant="primary">
                    Sign Off
                </flux:button>
            @endif
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-6 py-6 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-4xl mx-auto space-y-4">
            @forelse($conversation->messages as $message)
                <div
                    wire:key="message-{{ $message->id }}"
                    wire:transition.opacity.duration.200ms
                    class="{{ $message->isFromUser() ? 'max-w-2xl ml-auto' : 'max-w-2xl' }}"
                >
                    @if($message->isFromUser())
                        <flux:callout color="blue" icon="user-circle" heading="You">
                            {{ $message->content }}
                        </flux:callout>
                    @else
                        <flux:callout color="purple" icon="sparkles" heading="Reqqy">
                            {{ $message->content }}
                        </flux:callout>
                    @endif
                </div>
            @empty
                <div class="text-center py-12">
                    <flux:text class="text-gray-500 dark:text-gray-400">
                        Start the conversation by describing what you'd like to build
                    </flux:text>
                </div>
            @endforelse

            <div
                wire:loading
                wire:target="handleUserMessageCreated"
                class="max-w-2xl"
            >
                <flux:callout color="purple" icon="sparkles" heading="Reqqy">
                    Thinking through your request...
                </flux:callout>
            </div>

            @if($conversation->isSignedOff())
                <div class="max-w-2xl">
                    <flux:callout color="purple" icon="link" heading="Conversation Link">
                        <div class="space-y-2">
                            <flux:text>You can bookmark or share this link to revisit this conversation:</flux:text>
                            <flux:input
                                value="{{ route('conversation', ['conversation_id' => $conversation->id]) }}"
                                readonly
                                copyable
                            />
                        </div>
                    </flux:callout>
                </div>
            @endif
        </div>
    </div>

    @if(!$conversation->isSignedOff())
        <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
            <form wire:submit.prevent="sendMessage" class="max-w-4xl mx-auto">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <flux:textarea
                            wire:model="messageContent"
                            placeholder="Type your message..."
                            rows="3"
                        />
                    </div>
                    <div class="flex items-end">
                        <flux:button type="submit" variant="primary">
                            Send
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="bg-blue-50 dark:bg-blue-900/20 border-t border-blue-200 dark:border-blue-800 px-6 py-4">
            <div class="max-w-4xl mx-auto text-center">
                <flux:text class="text-blue-800 dark:text-blue-200">
                    This conversation has been signed off. An admin will review your requirements soon.
                </flux:text>
            </div>
        </div>
    @endif
</div>
