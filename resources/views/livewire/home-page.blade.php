<div class="max-w-4xl mx-auto py-12 px-4">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
            Welcome to Reqqy
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">
            What would you like to request today?
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <flux:modal.trigger name="select-application">
            <flux:card class="hover:shadow-lg transition-shadow cursor-pointer h-full">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon.puzzle-piece class="text-blue-600 dark:text-blue-400" />
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-3">
                        New Feature
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Request a new feature for an existing Laravel application
                    </p>
                </div>
            </flux:card>
        </flux:modal.trigger>

        <flux:card class="hover:shadow-lg transition-shadow cursor-pointer h-full" wire:click="startNewApplication">
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <flux:icon.sparkles class="text-green-600 dark:text-green-400" />
                </div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-3">
                    New Application
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Request an entirely new Laravel application
                </p>
            </div>
        </flux:card>
    </div>


    @if($conversations->count() > 0)

    <flux:separator class="mt-12"/>

    <flux:heading size="lg" class="mt-12">Recent Conversations</flux:heading>

    <div class="grid md:grid-cols-2 gap-6 mt-6">
        @foreach($conversations as $conversation)
            <a href="{{ route('conversation', ['conversation_id' => $conversation->id]) }}">
                <flux:card class="hover:bg-zinc-100 dark:hover:bg-zinc-700 cursor-pointer h-full">
                    <flux:heading size="lg">{{ $conversation->created_at->format('d/m/Y H:i') }}</flux:heading>
                    <flux:text>
                        {{ Str::limit($conversation->messages->first()->content, 50, '...') }}
                    </flux:text>
                </flux:card>
            </a>
        @endforeach
    </div>
    @endif
    <flux:modal name="select-application" variant="flyout" class="md:w-96">
        <form wire:submit="startNewFeature" class="space-y-6">
            <div>
                <flux:heading size="lg">Select Application</flux:heading>
                <flux:subheading class="mt-2">Choose which application you'd like to request a feature for</flux:subheading>
            </div>

            <flux:select wire:model="selectedApplicationId" variant="listbox" searchable placeholder="Choose Application...">
                @foreach($applications as $application)
                    <flux:select.option value="{{ $application->id }}">{{ $application->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Continue</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
