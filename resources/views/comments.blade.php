@use('\Kirschbaum\Commentions\Config')

<div class="comm:space-y-2" x-data="{ wasFocused: false }">
    @if (Config::resolveAuthenticatedUser()?->can('create', Config::getCommentModel()))
        <form wire:submit.prevent="save" x-cloak>
            {{-- tiptap editor --}}
            <div class="comm:relative tip-tap-container comm:mb-2" x-on:click="wasFocused = true" wire:ignore>
                <div
                    x-data="editor(@js($commentBody), @js($this->mentions), 'comments')"
                >
                    <div x-ref="element"></div>
                </div>
            </div>

            <template x-if="wasFocused">
                <div>
                    <x-filament::button
                        wire:click="save"
                        size="sm"
                    >Comment</x-filament::button>

                    <x-filament::button
                        x-on:click="wasFocused = false"
                        wire:click="clear"
                        size="sm"
                        color="gray"
                    >Cancel</x-filament::button>
                </div>
            </template>
        </form>
    @endif

    <livewire:commentions::comment-list
        :record="$record"
        :mentionables="$this->mentions"
        :polling-interval="$pollingInterval"
    />
</div>
