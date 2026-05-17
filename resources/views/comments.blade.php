@use('\Kirschbaum\Commentions\Config')

<div class="comm:flex comm:gap-4 comm:h-full" x-data="{ wasFocused: false }">
    {{-- Main Comments Area --}}
    <div class="comm:flex-1 comm:space-y-2">
        @if (Config::resolveAuthenticatedUser()?->can('create', Config::getCommentModel()))
            <form wire:submit.prevent="save" x-cloak>
                @if ($this->ratingsAreEnabled())
                    <div class="comm:mb-2 comm:flex comm:items-center comm:gap-0.5" x-data="{ hover: 0 }">
                        @for ($ratingStar = 1; $ratingStar <= $this->getMaxRating(); $ratingStar++)
                            <button
                                type="button"
                                wire:key="comment-rating-star-{{ $ratingStar }}"
                                x-on:click="$wire.rating = ($wire.rating === {{ $ratingStar }} ? null : {{ $ratingStar }})"
                                x-on:mouseenter="hover = {{ $ratingStar }}"
                                x-on:mouseleave="hover = 0"
                                class="commentions-rating-star"
                                :class="{ 'commentions-rating-star-active': (hover || $wire.rating) >= {{ $ratingStar }} }"
                            >
                                <x-filament::icon icon="heroicon-s-star" class="comm:h-5 comm:w-5" />
                            </button>
                        @endfor
                    </div>

                    @error('rating')
                        <p class="comm:mb-2 comm:text-xs comm:text-red-600">{{ $message }}</p>
                    @enderror
                @endif

                {{-- tiptap editor --}}
                <div class="comm:relative tip-tap-container comm:mb-2" x-on:click="wasFocused = true" wire:ignore>
                    <div
                        x-data="editor(@js($commentBody), @js($this->mentions), 'comments', @js($this->getPlaceholder()), @js($this->getTipTapCssClasses()), @js($commentionsComponentPrefix . 'comments'))"
                    >
                        <div x-ref="element"></div>
                    </div>
                </div>

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
