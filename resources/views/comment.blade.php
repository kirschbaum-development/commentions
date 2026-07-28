<div class="comm:flex comm:items-start comm:gap-x-4 comm:border comm:border-gray-300 comm:dark:border-gray-700 comm:p-4 comm:rounded-lg comm:shadow-sm comm:mb-2" id="filament-comment-{{ $comment->getId() }}">
    @if ($avatar = $comment->getAuthorAvatar())
        <img
            src="{{ $comment->getAuthorAvatar() }}"
            alt="{{ __('commentions::comments.user_avatar_alt') }}"
            class="comm:w-10 comm:h-10 comm:rounded-full comm:mt-0.5 comm:object-cover comm:object-center"
        />
    @else
        <div class="comm:w-10 comm:h-10 comm:rounded-full comm:mt-0.5 "></div>
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

            @if ($comment->isComment())
                <div class="comm:flex comm:gap-x-1">
                    {{ $this->editAction }}
                    {{ $this->deleteAction }}

                    @foreach ($this->getCustomActions() as $commentAction)
                        {{ $commentAction }}
                    @endforeach
                </div>
            @endif
        </div>

        @if ($editing)
            <div class="comm:flex-1 comm:space-y-2">
                <div
                    class="comm:mt-2"
                    x-cloak
                    role="form"
                    x-data="editor(@js($commentBody), @js($mentionables), 'comment', null, @js($this->getTipTapCssClasses()), @js($commentionsComponentPrefix . 'comment'))"
                >
                    {{-- tiptap editor --}}
                    <div class="comm:relative tip-tap-container comm:mb-2" wire:ignore>
                        <div x-ref="element"></div>
                    </div>

                    <div class="comm:flex comm:gap-x-2">
                        <x-filament::button
                            wire:click="updateComment({{ $comment->getId() }})"
                            x-bind:disabled="isEmpty"
                            x-bind:class="{ 'comm:opacity-50 comm:cursor-not-allowed': isEmpty }"
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
        @endif
    </div>

    <x-filament-actions::modals />
</div>
