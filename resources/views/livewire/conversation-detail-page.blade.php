<div class="max-w-6xl mx-auto py-12 px-4">
    <div class="mb-8">
        <flux:link :href="route('admin.conversations.index')" icon="arrow-left" class="mb-4">Back to Conversations</flux:link>
        <flux:heading size="xl" class="mt-4">Conversation Details</flux:heading>
    </div>

    <div class="space-y-6">
        {{-- Summary Section --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">Summary</flux:heading>
            <div class="grid gap-4">
                <div>
                    <flux:text class="font-medium">Type</flux:text>
                    <flux:text>
                        @if($conversation->application_id)
                            Feature Request
                        @else
                            New Application
                        @endif
                    </flux:text>
                </div>
                <div>
                    <flux:text class="font-medium">Initiated by</flux:text>
                    <flux:text>{{ $conversation->user->username }} ({{ $conversation->user->email }})</flux:text>
                </div>
                <div>
                    <flux:text class="font-medium">Created</flux:text>
                    <flux:text>{{ $conversation->created_at->format('M j, Y g:i A') }}</flux:text>
                </div>
                @if($conversation->application_id)
                    <div>
                        <flux:text class="font-medium">Application</flux:text>
                        <flux:text>App ID {{ $conversation->application_id }}</flux:text>
                    </div>
                @endif
                @if($conversation->signed_off_at)
                    <div>
                        <flux:text class="font-medium">Signed off</flux:text>
                        <flux:text>{{ $conversation->signed_off_at->format('M j, Y g:i A') }}</flux:text>
                    </div>
                @endif
                <div>
                    <flux:text class="font-medium">Messages</flux:text>
                    <flux:text>{{ $conversation->messages->count() }}</flux:text>
                </div>
                <div>
                    <flux:text class="font-medium">Documents</flux:text>
                    <flux:text>{{ $conversation->documents->count() }}</flux:text>
                </div>
            </div>
        </flux:card>

        {{-- Status Update Section --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">Status</flux:heading>
            <form wire:submit="updateStatus" class="space-y-4">
                <flux:radio.group wire:model.live="status" variant="pills" label="Conversation Status">
                    @foreach($statuses as $statusOption)
                        <flux:radio value="{{ $statusOption->value }}" label="{{ ucwords(str_replace('_', ' ', $statusOption->value)) }}" />
                    @endforeach
                </flux:radio.group>
                <flux:button type="submit" variant="primary">Update Status</flux:button>
            </form>
        </flux:card>

        {{-- Conversation History Section --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">Conversation History</flux:heading>
            @if($conversation->messages->count() > 0)
                @php
                    $sortedMessages = $conversation->messages->sortBy('created_at');
                    $messagesToShow = $showFullConversation ? $sortedMessages : $sortedMessages->take(3);
                @endphp
                <div class="space-y-4">
                    @foreach($messagesToShow as $message)
                        <div wire:key="message-{{ $message->id }}" class="{{ $message->isFromUser() ? 'max-w-2xl ml-auto' : 'max-w-2xl' }}">
                            @if($message->isFromUser())
                                <flux:callout color="blue" icon="user-circle" heading="{{ $conversation->user->username }}">
                                    {{ $message->content }}
                                </flux:callout>
                            @else
                                <flux:callout color="purple" icon="sparkles" heading="Reqqy">
                                    {{ $message->content }}
                                </flux:callout>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($conversation->messages->count() > 3)
                    <div class="mt-4">
                        <flux:button wire:click="$toggle('showFullConversation')" variant="ghost" icon="{{ $showFullConversation ? 'chevron-up' : 'chevron-down' }}">
                            {{ $showFullConversation ? 'Show less' : 'Show full conversation (' . ($conversation->messages->count() - 3) . ' more)' }}
                        </flux:button>
                    </div>
                @endif
            @else
                <flux:text>No messages in this conversation</flux:text>
            @endif
        </flux:card>

        {{-- Documents Section --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">Documents</flux:heading>
            @if($conversation->documents->count() > 0)
                <div class="space-y-4">
                    @foreach($conversation->documents as $document)
                        @php
                            $isExpanded = in_array($document->id, $expandedDocuments);
                        @endphp
                        <div wire:key="document-{{ $document->id }}" class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <flux:heading size="md">{{ $document->name }}</flux:heading>
                                    <flux:text class="text-sm">Created {{ $document->created_at->format('M j, Y g:i A') }}</flux:text>
                                </div>
                            </div>
                            <div class="mt-4 bg-zinc-50 dark:bg-zinc-900 rounded p-4 overflow-x-auto {{ $isExpanded ? '' : 'max-h-96 overflow-y-hidden relative' }}">
                                <pre class="text-sm whitespace-pre-wrap">{{ $document->content }}</pre>
                                @if(!$isExpanded)
                                    <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-zinc-50 dark:from-zinc-900 to-transparent"></div>
                                @endif
                            </div>
                            <div class="mt-2">
                                <flux:button wire:click="toggleDocument({{ $document->id }})" variant="ghost" size="sm" icon="{{ $isExpanded ? 'chevron-up' : 'chevron-down' }}">
                                    {{ $isExpanded ? 'Show less' : 'Show full document' }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text>No documents generated yet</flux:text>
            @endif
        </flux:card>
    </div>
</div>
