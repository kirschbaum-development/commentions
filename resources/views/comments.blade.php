@use('\Kirschbaum\Commentions\Config')

<div class="comm:flex comm:gap-4 comm:h-full" x-data="{ wasFocused: false }">
    {{-- Main Comments Area --}}
    <div class="comm:flex-1 comm:space-y-2">
        @if (Config::resolveAuthenticatedUser()?->can('create', Config::getCommentModel()))
            <form wire:submit.prevent="save" x-cloak>
                {{-- tiptap editor --}}
                <div class="comm:relative tip-tap-container comm:mb-2" x-on:click="wasFocused = true" wire:ignore>
                    <div
                        x-data="editor(@js($commentBody), @js($this->mentions), 'comments', @js($this->getPlaceholder()), @js($this->getTipTapCssClasses()), @js($commentionsComponentPrefix . 'comments'))"
                    >
                        <div x-ref="element"></div>
                    </div>
                </div>

            @if ($this->attachmentsAreEnabled())
                <div class="comm:mb-2 comm:space-y-1">
                    <label class="comm:inline-flex comm:cursor-pointer comm:items-center comm:gap-1 comm:rounded-lg comm:border comm:border-gray-300 comm:dark:border-gray-700 comm:px-2 comm:py-1 comm:text-xs comm:text-gray-600 comm:dark:text-gray-300 comm:hover:bg-gray-100 comm:dark:hover:bg-gray-800">
                        <input type="file" class="comm:hidden" wire:model="attachments" multiple />
                        <x-filament::icon icon="heroicon-s-paper-clip" class="comm:h-4 comm:w-4" />
                        <span>{{ __('commentions::comments.attach_files') }}</span>
                    </label>

                    <div wire:loading wire:target="attachments" class="comm:text-xs comm:text-gray-500">
                        {{ __('commentions::comments.uploading') }}
                    </div>

                    @error('attachments')
                        <p class="comm:text-xs comm:text-red-600">{{ $message }}</p>
                    @enderror
                    @error('attachments.*')
                        <p class="comm:text-xs comm:text-red-600">{{ $message }}</p>
                    @enderror

                    @if (! empty($attachments))
                        <ul class="comm:space-y-1">
                            @foreach ($attachments as $attachmentIndex => $pendingAttachment)
                                <li class="comm:flex comm:items-center comm:gap-1.5 comm:text-xs comm:text-gray-600 comm:dark:text-gray-300">
                                    <x-filament::icon icon="heroicon-s-document" class="comm:h-4 comm:w-4 comm:flex-shrink-0" />
                                    <span class="comm:truncate">{{ $pendingAttachment->getClientOriginalName() }}</span>
                                    <button
                                        type="button"
                                        wire:click="removeAttachment({{ $attachmentIndex }})"
                                        class="comm:text-red-600 comm:hover:text-red-700"
                                    >
                                        <x-filament::icon icon="heroicon-s-x-mark" class="comm:h-4 comm:w-4" />
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <template x-if="wasFocused">
                <div>
                    <x-filament::button
                        wire:click="save"
                        size="sm"
                    >{{ __('commentions::comments.comment') }}</x-filament::button>

                    <x-filament::button
                        x-on:click="wasFocused = false"
                        wire:click="clear"
                        size="sm"
                        color="gray"
                    >{{ __('commentions::comments.cancel') }}</x-filament::button>
                </div>
            </template>
        </form>
    @endif

        <livewire:dynamic-component
            :component="$commentionsComponentPrefix . 'comment-list'"
            :record="$record"
            :mentionables="$this->mentions"
            :polling-interval="$pollingInterval"
            :paginate="$paginate ?? true"
            :per-page="$perPage ?? 5"
            :load-more-label="$loadMoreLabel ?? __('commentions::comments.show_more')"
            :per-page-increment="$perPageIncrement ?? null"
            :tip-tap-css-classes="$tipTapCssClasses"
        />
    </div>

    {{-- Subscription Sidebar --}}
    @if ($this->canSubscribe && $this->resolvedSidebarEnabled)
        <livewire:dynamic-component
            :component="$commentionsComponentPrefix . 'subscription-sidebar'"
            :record="$record"
            :show-subscribers="$this->resolvedShowSubscribers"
        />
    @endif
</div>
