<div class="max-w-6xl mx-auto py-12 px-4">
    <div class="mb-8">
        <flux:heading size="xl">Conversations</flux:heading>
        <flux:text class="mt-2">Review and manage all conversation requests</flux:text>
    </div>

    @if($conversations->count() > 0)
        <div class="grid gap-4">
            @foreach($conversations as $conversation)
                <flux:card>
                    <a href="{{ route('admin.conversations.show', $conversation) }}" class="block cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 -m-6 p-6 rounded-lg transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <flux:heading size="lg">
                                        @if($conversation->application_id)
                                            Feature Request
                                        @else
                                            New Application
                                        @endif
                                    </flux:heading>
                                    <flux:badge :color="match($conversation->status->value) {
                                        'pending' => 'yellow',
                                        'in_review' => 'blue',
                                        'approved' => 'green',
                                        'rejected' => 'red',
                                        'completed' => 'zinc',
                                        default => 'zinc'
                                    }">
                                        {{ ucwords(str_replace('_', ' ', $conversation->status->value)) }}
                                    </flux:badge>
                                </div>

                                @if($conversation->title)
                                    <div class="mt-2">
                                        <flux:text variant="strong">{{ $conversation->title }}</flux:text>
                                    </div>
                                @endif

                                <div class="mt-3 flex flex-wrap gap-4 text-sm">
                                    <flux:text>
                                        <span class="font-medium">Initiated by:</span> {{ $conversation->user->username }}
                                    </flux:text>
                                    <flux:text>
                                        <span class="font-medium">Created:</span> {{ $conversation->created_at->format('M j, Y g:i A') }}
                                    </flux:text>
                                    @if($conversation->application_id)
                                        <flux:text>
                                            <span class="font-medium">App ID:</span> {{ $conversation->application_id }}
                                        </flux:text>
                                    @endif
                                    @if($conversation->signed_off_at)
                                        <flux:text>
                                            <span class="font-medium">Signed off:</span> {{ $conversation->signed_off_at->format('M j, Y g:i A') }}
                                        </flux:text>
                                    @endif
                                </div>

                                <div class="mt-3 flex gap-4 text-sm">
                                    <flux:text>
                                        <span class="font-medium">Messages:</span> {{ $conversation->messages->count() }}
                                    </flux:text>
                                    <flux:text>
                                        <span class="font-medium">Documents:</span> {{ $conversation->documents->count() }}
                                    </flux:text>
                                </div>
                            </div>
                            <flux:icon.chevron-right class="size-5 text-zinc-400" />
                        </div>
                    </a>
                </flux:card>
            @endforeach
        </div>
    @else
        <flux:card>
            <div class="text-center py-12">
                <flux:icon.chat-bubble-left-right class="size-12 mx-auto mb-4 text-zinc-400" />
                <flux:heading size="lg" class="mb-2">No conversations yet</flux:heading>
                <flux:text>Conversations will appear here once users start requesting features or applications</flux:text>
            </div>
        </flux:card>
    @endif
</div>
