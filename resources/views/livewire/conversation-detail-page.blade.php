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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($conversation->documents as $document)
                        <flux:modal.trigger :name="'document-' . $document->id">
                            <flux:card class="cursor-pointer hover:border-zinc-400 dark:hover:border-zinc-500 transition">
                                <div>
                                    <flux:heading size="md" class="mb-2">{{ $document->name }}</flux:heading>
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Created {{ $document->created_at->format('M j, Y g:i A') }}
                                    </flux:text>
                                    <div class="mt-4">
                                        <flux:badge color="zinc" icon="document-text">
                                            {{ number_format(strlen($document->content)) }} chars
                                        </flux:badge>
                                    </div>
                                </div>
                            </flux:card>
                        </flux:modal.trigger>
                    @endforeach
                </div>

                {{-- Modals placed outside grid --}}
                @foreach($conversation->documents as $document)
                    <flux:modal :name="'document-' . $document->id" class="w-full max-w-4xl">
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <flux:button wire:click="downloadDocument({{ $document->id }})" icon="arrow-down-tray" size="sm" square tooltip="Download markdown file" />
                                <flux:button wire:click="downloadDocumentAsHtml({{ $document->id }})" icon="globe-alt" size="sm" square tooltip="Download HTML file" />
                                <div>
                                    <flux:heading size="lg">{{ $document->name }}</flux:heading>
                                    <flux:text class="mt-1 text-sm">
                                        Created {{ $document->created_at->format('M j, Y g:i A') }}
                                    </flux:text>
                                </div>
                            </div>

                            <flux:separator />

                            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 max-h-[70vh] overflow-y-auto">
                                <pre class="text-sm whitespace-pre-wrap">{{ $document->content }}</pre>
                            </div>
                        </div>
                    </flux:modal>
                @endforeach
            @else
                <flux:text>No documents generated yet</flux:text>
            @endif
        </flux:card>
    </div>
</div>
