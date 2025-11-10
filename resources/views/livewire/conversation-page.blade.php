<div class="space-y-6" wire:poll.50s>
    <div class="flex flex-row items-center justify-between">
        <flux:heading size="xl">
            @if($conversation->application)
                {{ $conversation->application->name }} - New Feature
            @else
                New Application Request
            @endif
        </flux:heading>
        {{-- TEMPORARY: Always show sign-off button for debugging --}}
        {{-- @if(!$conversation->isSignedOff()) --}}
            <flux:button wire:click="signOff" variant="primary">
                Sign Off {{ $conversation->isSignedOff() ? '(Re-trigger for debugging)' : '' }}
            </flux:button>
        {{-- @endif --}}
    </div>

    @if($conversationMessages->isNotEmpty())
        <flux:card>
            <div class="max-w-4xl mx-auto space-y-4">
                @foreach($conversationMessages as $message)
                    <div wire:key="message-{{ $message['id'] }}" class="{{ $message['is_from_user'] ? 'max-w-2xl ml-auto' : 'max-w-2xl' }}">
                        @if($message['is_from_user'])
                            <flux:callout color="blue" icon="user-circle" heading="You">
                                {{ $message['content'] }}
                            </flux:callout>
                        @else
                            <flux:callout color="purple" icon="sparkles" heading="Reqqy">
                                @if($message['is_pending'])
                                    <div class="flex items-center gap-2">
                                        <flux:icon.loading class="size-4" />
                                        <span>{{ $message['content'] }}</span>
                                    </div>
                                @else
                                    {{ $message['content'] }}
                                @endif
                            </flux:callout>
                        @endif
                    </div>
                @endforeach

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
        </flux:card>
    @endif

    @if(!$conversation->isSignedOff())
        <div class="{{ $conversationMessages->isEmpty() ? 'flex items-center justify-center min-h-[60vh]' : '' }}">
            <div class="max-w-2xl mx-auto w-full px-6">
                @if($conversationMessages->isEmpty())
                    <div class="text-center mb-6">
                        <flux:subheading>Start the conversation by describing what you'd like to build</flux:subheading>
                    </div>
                @endif
                <form wire:submit.prevent="sendMessage">
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
