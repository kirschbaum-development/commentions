@use('\Kirschbaum\Commentions\Config')

<div
    @class([
        'comm:flex comm:items-start',
        'comm:gap-x-4 comm:border comm:border-gray-300 comm:dark:border-gray-700 comm:p-4 comm:rounded-lg comm:shadow-sm comm:mb-2' => $depth === 0,
        'comm:relative comm:gap-x-3 comm:py-2 comm:pl-6' => $depth > 0,
    ])
    id="filament-comment-{{ $comment->getId() }}"
>
    @if ($depth > 0)
        <div class="commentions-thread" aria-hidden="true"></div>
    @endif

    @if ($avatar = $comment->getAuthorAvatar())
        <img
            src="{{ $comment->getAuthorAvatar() }}"
            alt="{{ __('commentions::comments.user_avatar_alt') }}"
            @class([
                'comm:rounded-full comm:mt-0.5 comm:object-cover comm:object-center',
                'comm:w-10 comm:h-10' => $depth === 0,
                'comm:w-7 comm:h-7' => $depth > 0,
            ])
        />
    @else
        <div @class([
            'comm:rounded-full comm:mt-0.5',
            'comm:w-10 comm:h-10' => $depth === 0,
            'comm:w-7 comm:h-7' => $depth > 0,
        ])></div>
    @endif

    <div class="comm:flex-1">
        <div class="comm:text-sm comm:font-bold comm:text-gray-900 comm:dark:text-gray-100 comm:flex comm:justify-between comm:items-center">
            <div>
                {{ $comment->getAuthorName() }}
                <span
                    class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-300"
                    title="{{ __('commentions::comments.commented_at', ['datetime' => $comment->getCreatedAt()->format('Y-m-d H:i:s')]) }}"
                >{{ $comment->getCreatedAt()->diffForHumans() }}</span>

                @if ($comment->getUpdatedAt()->gt($comment->getCreatedAt()))
                    <span
                        class="comm:text-xs comm:text-gray-300 comm:ml-1"
                        title="{{ __('commentions::comments.edited_at', ['datetime' => $comment->getUpdatedAt()->format('Y-m-d H:i:s')]) }}"
                    >({{ __('commentions::comments.edited') }})</span>
                @endif

                @if ($comment->getLabel())
                    <span class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-300 comm:bg-gray-100 comm:dark:bg-gray-800 comm:px-1.5 comm:py-0.5 comm:rounded-md">
                        {{ $comment->getLabel() }}
                    </span>
                @endif
            </div>

            @if ($comment->isComment() && ($this->canReply() || Config::resolveAuthenticatedUser()?->canAny(['update', 'delete'], $comment)))
                <div class="comm:flex comm:gap-x-1">
                    @if ($this->canReply())
                        <x-filament::icon-button
                            icon="heroicon-s-arrow-uturn-left"
                            wire:click="reply"
                            size="xs"
                            color="gray"
                        />
                    @endif

                    @if (Config::resolveAuthenticatedUser()?->can('update', $comment))
                        <x-filament::icon-button
                            icon="heroicon-s-pencil-square"
                            wire:click="edit"
                            size="xs"
                            color="gray"
                        />
                    @endif

                    @if (Config::resolveAuthenticatedUser()?->can('delete', $comment))
                        <x-filament::modal
                            id="delete-comment-modal-{{ $comment->getId() }}"
                            width="sm"
                        >
                            <x-slot name="trigger">
                                <x-filament::icon-button
                                    icon="heroicon-s-trash"
                                    size="xs"
                                    color="gray"
                                />
                            </x-slot>

                            <x-slot name="heading">
                                {{ __('commentions::comments.delete_comment_heading') }}
                            </x-slot>

                            <div class="comm:py-4">
                                {{ __('commentions::comments.delete_comment_body') }}
                            </div>

                            <x-slot name="footer">
                                <div class="comm:flex comm:justify-end comm:gap-x-4">
                                    <x-filament::button
                                        wire:click="$dispatch('close-modal', { id: 'delete-comment-modal-{{ $comment->getId() }}' })"
                                        color="gray"
                                    >
                                        {{ __('commentions::comments.cancel') }}
                                    </x-filament::button>

                                    <x-filament::button
                                        wire:click="delete"
                                        color="danger"
                                    >
                                        {{ __('commentions::comments.delete') }}
                                    </x-filament::button>
                                </div>
                            </x-slot>
                        </x-filament::modal>
                    @endif
                </div>
            @endif
        </div>

        @if ($editing)
            <div class="comm:mt-2">
                <div class="tip-tap-container comm:mb-2" wire:ignore>
                    <div x-data="editor(@js($commentBody), @js($mentionables), 'comment', null, @js($this->getTipTapCssClasses()), @js($commentionsComponentPrefix . 'comment'))">
                        <div x-ref="element"></div>
                    </div>
                </div>

                <div class="comm:flex comm:gap-x-2">
                    <x-filament::button
                        wire:click="updateComment({{ $comment->getId() }})"
                        size="sm"
                    >
                        {{ __('commentions::comments.save') }}
                    </x-filament::button>

                    <x-filament::button
                        wire:click="cancelEditing"
                        size="sm"
                        color="gray"
                    >
                        {{ __('commentions::comments.cancel') }}
                    </x-filament::button>
                </div>
            </div>
        @else
            <div class="comm:mt-1 comm:space-y-6 comm:text-sm comm:text-gray-800 comm:dark:text-gray-200">{!! $comment->getParsedBody() !!}</div>

            @if ($comment->isComment())
                <livewire:dynamic-component
                    :component="$commentionsComponentPrefix . 'reactions'"
                    :comment="$comment"
                    :wire:key="'reaction-manager-' . $comment->getId()"
                />
            @endif

            @if ($replying)
                <div class="comm:mt-3">
                    <div class="tip-tap-container comm:mb-2" wire:ignore>
                        <div x-data="editor(@js($commentBody), @js($mentionables), 'comment', @js(__('commentions::comments.placeholder')), @js($this->getTipTapCssClasses()), @js($commentionsComponentPrefix . 'comment'))">
                            <div x-ref="element"></div>
                        </div>
                    </div>

                    <div class="comm:flex comm:gap-x-2">
                        <x-filament::button wire:click="saveReply" size="sm">
                            {{ __('commentions::comments.reply') }}
                        </x-filament::button>

                        <x-filament::button wire:click="cancelReplying" size="sm" color="gray">
                            {{ __('commentions::comments.cancel') }}
                        </x-filament::button>
                    </div>
                </div>
            @endif

            @if ($comment->isComment() && config('commentions.threading.enabled', false) && $comment->replies->isNotEmpty())
                <div x-data="{ expanded: true }" class="comm:mt-3">
                    <button
                        type="button"
                        @click="expanded = !expanded"
                        :aria-expanded="expanded ? 'true' : 'false'"
                        aria-controls="comment-replies-{{ $comment->getId() }}"
                        class="comm:flex comm:items-center comm:gap-x-1 comm:text-xs comm:font-medium comm:text-gray-500 comm:dark:text-gray-400 comm:hover:text-gray-700 comm:dark:hover:text-gray-200 comm:focus:outline-none comm:focus-visible:ring-2 comm:focus-visible:ring-blue-500 comm:rounded comm:mb-1"
                    >
                        <x-filament::icon
                            icon="heroicon-m-chevron-down"
                            class="comm:w-4 comm:h-4 comm:transition-transform"
                            x-bind:class="expanded ? '' : 'comm:-rotate-90'"
                        />
                        <span x-show="expanded">{{ __('commentions::comments.hide_replies') }}</span>
                        <span x-show="!expanded" x-cloak>{{ trans_choice('commentions::comments.replies_count', $comment->repliesCount(), ['count' => $comment->repliesCount()]) }}</span>
                    </button>

                    <div
                        id="comment-replies-{{ $comment->getId() }}"
                        role="group"
                        aria-label="{{ trans_choice('commentions::comments.replies_count', $comment->repliesCount(), ['count' => $comment->repliesCount()]) }}"
                        x-show="expanded"
                        x-collapse
                        @class([
                            'commentions-replies',
                            'comm:pl-3' => $this->shouldIndentReplies(),
                        ])
                    >
                        @foreach ($comment->replies as $reply)
                            <livewire:dynamic-component
                                :component="$commentionsComponentPrefix . 'comment'"
                                :key="'reply-' . $reply->getContentHash()"
                                :comment="$reply"
                                :depth="$depth + 1"
                                :mentionables="$mentionables"
                                :tip-tap-css-classes="$tipTapCssClasses"
                            />
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
