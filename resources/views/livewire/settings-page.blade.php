<div class="max-w-6xl mx-auto py-12 px-4">
    <div class="flex items-center justify-between mb-8">
        <div>
            <flux:heading size="xl">Settings</flux:heading>
            <flux:text class="mt-2">Manage your Laravel applications</flux:text>
        </div>
    </div>

    <flux:tab.group>
        <flux:tabs wire:model="activeTab">
            <flux:tab name="internal">Internal ({{ $internalApplications->count() }})</flux:tab>
            <flux:tab name="external">External ({{ $externalApplications->count() }})</flux:tab>
            <flux:tab name="proposed">Proposed ({{ $proposedApplications->count() }})</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="internal">
            <div class="mt-6">
                <div class="flex justify-end mb-4">
                    <flux:modal.trigger name="create-application">
                        <flux:button wire:click="createApplication" variant="primary" icon="plus">Add Internal Application</flux:button>
                    </flux:modal.trigger>
                </div>

                @if($internalApplications->count() > 0)
                    <div class="grid gap-4">
                        @foreach($internalApplications as $application)
                            <flux:card>
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <flux:heading size="lg">{{ $application->name }}</flux:heading>
                                        @if($application->short_description)
                                            <flux:text class="mt-1">{{ $application->short_description }}</flux:text>
                                        @endif
                                        <div class="mt-3 flex flex-wrap gap-4 text-sm">
                                            @if($application->status)
                                                <flux:text>
                                                    <span class="font-medium">Status:</span> {{ $application->status }}
                                                </flux:text>
                                            @endif
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
                                            <flux:button wire:click="editApplication({{ $application->id }})" size="sm" icon="pencil" square iconOnly />
                                        </flux:modal.trigger>
                                        <flux:button
                                            wire:click="deleteApplication({{ $application->id }})"
                                            wire:confirm="Are you sure you want to delete this application?"
                                            size="sm"
                                            variant="danger"
                                            icon="trash"
                                            square
                                            iconOnly />
                                    </div>
                                </div>
                            </flux:card>

                            <flux:modal name="edit-application-{{ $application->id }}" variant="flyout" class="md:w-96">
                                <form wire:submit="updateApplication" class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">Edit Internal Application</flux:heading>
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
                            <flux:heading size="lg" class="mb-2">No applications in this category</flux:heading>
                            <flux:text>Get started by adding a new application</flux:text>
                        </div>
                    </flux:card>
                @endif
            </div>
        </flux:tab.panel>

        <flux:tab.panel name="external">
            <div class="mt-6">
                <div class="flex justify-end mb-4">
                    <flux:modal.trigger name="create-application">
                        <flux:button wire:click="createApplication" variant="primary" icon="plus">Add External Application</flux:button>
                    </flux:modal.trigger>
                </div>

                @if($externalApplications->count() > 0)
                    <div class="grid gap-4">
                        @foreach($externalApplications as $application)
                            <flux:card>
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <flux:heading size="lg">{{ $application->name }}</flux:heading>
                                        @if($application->short_description)
                                            <flux:text class="mt-1">{{ $application->short_description }}</flux:text>
                                        @endif
                                        @if($application->url)
                                            <div class="mt-3">
                                                <flux:text>
                                                    <span class="font-medium">URL:</span> <flux:link :href="$application->url" target="_blank">{{ $application->url }}</flux:link>
                                                </flux:text>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        <flux:modal.trigger name="edit-application-{{ $application->id }}">
                                            <flux:button wire:click="editApplication({{ $application->id }})" size="sm" icon="pencil" square iconOnly />
                                        </flux:modal.trigger>
                                        <flux:button
                                            wire:click="deleteApplication({{ $application->id }})"
                                            wire:confirm="Are you sure you want to delete this application?"
                                            size="sm"
                                            variant="danger"
                                            icon="trash"
                                            square
                                            iconOnly />
                                    </div>
                                </div>
                            </flux:card>

                            <flux:modal name="edit-application-{{ $application->id }}" variant="flyout" class="md:w-96">
                                <form wire:submit="updateApplication" class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">Edit External Application</flux:heading>
                                        <flux:subheading class="mt-2">Update the application details</flux:subheading>
                                    </div>

                                    <flux:input wire:model="name" label="Name" placeholder="Application name" />
                                    <flux:textarea wire:model="short_description" label="Short Description" placeholder="Brief description" rows="2" />
                                    <flux:input wire:model="url" label="URL" placeholder="https://example.com" />

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
                            <flux:heading size="lg" class="mb-2">No applications in this category</flux:heading>
                            <flux:text>Get started by adding a new application</flux:text>
                        </div>
                    </flux:card>
                @endif
            </div>
        </flux:tab.panel>

        <flux:tab.panel name="proposed">
            <div class="mt-6">
                @if($proposedApplications->count() > 0)
                    <div class="grid gap-4">
                        @foreach($proposedApplications as $application)
                            <flux:card>
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <flux:heading size="lg">{{ $application->name }}</flux:heading>
                                        @if($application->short_description)
                                            <flux:text class="mt-1">{{ $application->short_description }}</flux:text>
                                        @endif
                                        @if($application->sourceConversation)
                                            <div class="mt-3">
                                                <flux:text class="text-sm">
                                                    <span class="font-medium">From conversation:</span>
                                                    <flux:link :href="route('conversation', ['conversation_id' => $application->sourceConversation->id])">View conversation</flux:link>
                                                </flux:text>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        <flux:modal.trigger name="promote-application-{{ $application->id }}">
                                            <flux:button wire:click="promoteApplication({{ $application->id }})" size="sm" icon="arrow-up" variant="primary" square iconOnly />
                                        </flux:modal.trigger>
                                        <flux:modal.trigger name="edit-application-{{ $application->id }}">
                                            <flux:button wire:click="editApplication({{ $application->id }})" size="sm" icon="pencil" square iconOnly />
                                        </flux:modal.trigger>
                                        <flux:button
                                            wire:click="deleteApplication({{ $application->id }})"
                                            wire:confirm="Are you sure you want to reject this proposal?"
                                            size="sm"
                                            variant="danger"
                                            icon="trash"
                                            square
                                            iconOnly />
                                    </div>
                                </div>
                            </flux:card>

                            <flux:modal name="promote-application-{{ $application->id }}" variant="flyout" class="md:w-96">
                                <form wire:submit="savePromotion" class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">Promote to Internal Application</flux:heading>
                                        <flux:subheading class="mt-2">Add additional details to promote this proposal</flux:subheading>
                                    </div>

                                    <flux:input wire:model="name" label="Name" readonly disabled />
                                    <flux:textarea wire:model="short_description" label="Short Description" readonly disabled rows="2" />

                                    <flux:separator />

                                    <flux:textarea wire:model="overview" label="Overview" placeholder="Detailed overview" rows="3" />
                                    <flux:checkbox wire:model="is_automated" label="Is Automated" />
                                    <flux:input wire:model="status" label="Status" placeholder="e.g., Active, Development" />
                                    <flux:input wire:model="url" label="URL" placeholder="https://example.com" />
                                    <flux:input wire:model="repo" label="Repository" placeholder="https://github.com/user/repo" />

                                    <div class="flex gap-2">
                                        <flux:spacer />
                                        <flux:button type="button" variant="ghost" x-on:click="$flux.modal('promote-application-{{ $application->id }}').close()">Cancel</flux:button>
                                        <flux:button type="submit" variant="primary">Promote Application</flux:button>
                                    </div>
                                </form>
                            </flux:modal>

                            <flux:modal name="edit-application-{{ $application->id }}" variant="flyout" class="md:w-96">
                                <form wire:submit="updateApplication" class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">Edit Proposed Application</flux:heading>
                                        <flux:subheading class="mt-2">Update the application details</flux:subheading>
                                    </div>

                                    <flux:input wire:model="name" label="Name" placeholder="Application name" />
                                    <flux:textarea wire:model="short_description" label="Short Description" placeholder="Brief description" rows="2" />

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
                            <flux:heading size="lg" class="mb-2">No applications in this category</flux:heading>
                            <flux:text>Get started by adding a new application</flux:text>
                        </div>
                    </flux:card>
                @endif
            </div>
        </flux:tab.panel>
    </flux:tab.group>

    <flux:modal name="create-application" variant="flyout" class="md:w-96">
        <form wire:submit="saveApplication" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    Add
                    @if($formCategory === 'internal')
                        Internal
                    @elseif($formCategory === 'external')
                        External
                    @elseif($formCategory === 'proposed')
                        Proposed
                    @endif
                    Application
                </flux:heading>
                <flux:subheading class="mt-2">Register a new application</flux:subheading>
            </div>

            <flux:input wire:model="name" label="Name" placeholder="Application name" />
            <flux:textarea wire:model="short_description" label="Short Description" placeholder="Brief description" rows="2" />

            @if($formCategory === 'internal')
                <flux:textarea wire:model="overview" label="Overview" placeholder="Detailed overview" rows="3" />
                <flux:checkbox wire:model="is_automated" label="Is Automated" />
                <flux:input wire:model="status" label="Status" placeholder="e.g., Active, Development" />
                <flux:input wire:model="url" label="URL" placeholder="https://example.com" />
                <flux:input wire:model="repo" label="Repository" placeholder="https://github.com/user/repo" />
            @elseif($formCategory === 'external')
                <flux:input wire:model="url" label="URL" placeholder="https://example.com" />
            @endif

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-application').close()">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Save Application</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
