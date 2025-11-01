<div class="max-w-6xl mx-auto py-12 px-4">
    <div class="flex items-center justify-between mb-8">
        <div>
            <flux:heading size="xl">Settings</flux:heading>
            <flux:text class="mt-2">Manage your Laravel applications</flux:text>
        </div>
        <flux:modal.trigger name="create-application">
            <flux:button wire:click="createApplication" variant="primary" icon="plus">Add Application</flux:button>
        </flux:modal.trigger>
    </div>

    @if($applications->count() > 0)
        <div class="grid gap-4">
            @foreach($applications as $application)
                <flux:card>
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <flux:heading size="lg">{{ $application->name }}</flux:heading>
                            @if($application->short_description)
                                <flux:text class="mt-1">{{ $application->short_description }}</flux:text>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-4 text-sm">
                                <flux:text>
                                    <span class="font-medium">Status:</span> {{ $application->status }}
                                </flux:text>
                                @if($application->is_automated)
                                    <flux:badge color="green">Automated</flux:badge>
                                @endif
                                @if($application->url)
                                    <flux:text>
                                        <span class="font-medium">URL:</span> <flux:link :href="$application->url" target="_blank">{{ $application->url }}</flux:link>
                                    </flux:text>
                                @endif
                                @if($application->repo)
                                    <flux:text>
                                        <span class="font-medium">Repo:</span> <flux:link :href="$application->repo" target="_blank">{{ $application->repo }}</flux:link>
                                    </flux:text>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <flux:modal.trigger name="edit-application-{{ $application->id }}">
                                <flux:button wire:click="editApplication({{ $application->id }})" size="sm" icon="pencil">Edit</flux:button>
                            </flux:modal.trigger>
                            <flux:button
                                wire:click="deleteApplication({{ $application->id }})"
                                wire:confirm="Are you sure you want to delete this application?"
                                size="sm"
                                variant="danger"
                                icon="trash">
                                Delete
                            </flux:button>
                        </div>
                    </div>
                </flux:card>

                <flux:modal name="edit-application-{{ $application->id }}" variant="flyout" class="md:w-96">
                    <form wire:submit="updateApplication" class="space-y-6">
                        <div>
                            <flux:heading size="lg">Edit Application</flux:heading>
                            <flux:subheading class="mt-2">Update the application details</flux:subheading>
                        </div>

                        <flux:input wire:model="name" label="Name" placeholder="Application name" />
                        <flux:textarea wire:model="short_description" label="Short Description" placeholder="Brief description" rows="2" />
                        <flux:textarea wire:model="overview" label="Overview" placeholder="Detailed overview" rows="3" />
                        <flux:checkbox wire:model="is_automated" label="Is Automated" />
                        <flux:input wire:model="status" label="Status" placeholder="e.g., Active, Development" />
                        <flux:input wire:model="url" label="URL" placeholder="https://example.com" />
                        <flux:input wire:model="repo" label="Repository" placeholder="https://github.com/user/repo" />

                        <div class="flex gap-2">
                            <flux:spacer />
                            <flux:button type="button" variant="ghost" x-on:click="$flux.modal('edit-application-{{ $application->id }}').close()">Cancel</flux:button>
                            <flux:button type="submit" variant="primary">Update Application</flux:button>
                        </div>
                    </form>
                </flux:modal>
            @endforeach
        </div>
    @else
        <flux:card>
            <div class="text-center py-12">
                <flux:icon.folder-open class="size-12 mx-auto mb-4 text-zinc-400" />
                <flux:heading size="lg" class="mb-2">No applications yet</flux:heading>
                <flux:text>Get started by adding your first Laravel application</flux:text>
            </div>
        </flux:card>
    @endif

    <flux:modal name="create-application" variant="flyout" class="md:w-96">
        <form wire:submit="saveApplication" class="space-y-6">
            <div>
                <flux:heading size="lg">Add Application</flux:heading>
                <flux:subheading class="mt-2">Register a new Laravel application</flux:subheading>
            </div>

            <flux:input wire:model="name" label="Name" placeholder="Application name" />
            <flux:textarea wire:model="short_description" label="Short Description" placeholder="Brief description" rows="2" />
            <flux:textarea wire:model="overview" label="Overview" placeholder="Detailed overview" rows="3" />
            <flux:checkbox wire:model="is_automated" label="Is Automated" />
            <flux:input wire:model="status" label="Status" placeholder="e.g., Active, Development" />
            <flux:input wire:model="url" label="URL" placeholder="https://example.com" />
            <flux:input wire:model="repo" label="Repository" placeholder="https://github.com/user/repo" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-application').close()">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Save Application</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
