<div class="flex items-start gap-x-4 border p-4 rounded-lg shadow-sm mb-2" id="filament-comment-{{ $comment->getId() }}">
    @if ($avatar = $comment->getAuthorAvatar())
        <img
            src="{{ $comment->getAuthorAvatar() }}"
            alt="User Avatar"
            class="w-10 h-10 rounded-full mt-0.5 object-cover object-center"
        />
    @else
        <div class="w-10 h-10 rounded-full mt-0.5 "></div>
    @endif

    <div class="flex-1">
        <div class="text-sm font-bold text-gray-900 dark:text-gray-100 flex justify-between items-center">
            <div>
                {{ $comment->getAuthorName() }}
                <span
                    class="text-xs text-gray-500 dark:text-gray-300"
                    title="Commented at {{ $comment->getCreatedAt()->format('Y-m-d H:i:s') }}"
                >{{ $comment->getCreatedAt()->diffForHumans() }}</span>

                @if ($comment->getUpdatedAt()->gt($comment->getCreatedAt()))
                    <span
                        class="text-xs text-gray-300 ml-1"
                        title="Edited at {{ $comment->getUpdatedAt()->format('Y-m-d H:i:s') }}"
                    >(edited)</span>
                @endif

                @if ($comment->getLabel())
                    <span class="text-xs text-gray-500 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-md">
                        {{ $comment->getLabel() }}
                    </span>
                @endif
            </div>

            @if ($comment->isComment() && $comment->canEdit())
                <div class="flex gap-x-1">
                    <x-filament::icon-button
                        icon="heroicon-s-pencil-square"
                        wire:click="edit"
                        size="xs"
                        color="gray"
                    />

                    @if ($comment->canDelete())
                        <x-filament::icon-button
                            icon="heroicon-s-trash"
                            wire:click="$dispatch('open-modal', { id: 'delete-comment-modal-{{ $comment->getId() }}' })"
                            size="xs"
                            color="gray"
                        />
                    @endif
                </div>
            @endif
        </div>

        @if ($editing)
            <div class="mt-2">
                <div class="tip-tap-container mb-2" wire:ignore>
                    <div x-data="editor(@js($commentBody), @js($mentionables), 'comment')">
                        <div x-ref="element"></div>
                    </div>
                </div>

                <div class="flex gap-x-2">
                    <x-filament::button
                        wire:click="updateComment({{ $comment->getId() }})"
                        size="sm"
                    >
                        Save
                    </x-filament::button>

                    <x-filament::button
                        wire:click="cancelEditing"
                        size="sm"
                        color="gray"
                    >
                        Cancel
                    </x-filament::button>
                </div>
            </div>
        @else
            <div class="mt-1 space-y-6 text-sm text-gray-800 dark:text-gray-200">{!! $comment->getParsedBody() !!}</div>

            @if ($comment->isComment() && $comment instanceof \Kirschbaum\Commentions\Comment)
                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center gap-x-2">
                    @php
                        $reactions = $this->reactionSummary();
                        $thumbsUpData = $reactions['👍'] ?? ['count' => 0, 'reacted_by_current_user' => false];
                    @endphp
                    <button
                        wire:click="toggleReaction('👍')"
                        type="button"
                        @disabled(! auth()->check())
                        class="inline-flex items-center justify-center gap-1 rounded-full border px-2 py-1 text-sm font-medium transition hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed
                               {{ $thumbsUpData['reacted_by_current_user']
                                   ? 'bg-primary-100 dark:bg-primary-800 border-primary-300 dark:border-primary-600 text-primary-700 dark:text-primary-200'
                                   : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}"
                        title="Thumbs Up"
                    >
                        <span>👍</span>
                        @if ($thumbsUpData['count'] > 0)
                            <span wire:key="reaction-count-thumbs-up-{{ $comment->getId() }}">{{ $thumbsUpData['count'] }}</span>
                        @endif
                    </button>

                    @foreach ($reactions as $reaction => $data)
                        @if ($reaction !== '👍' && $data['count'] > 0)
                            <span
                                wire:key="reaction-{{ $reaction }}-{{ $comment->getId() }}"
                                class="inline-flex items-center justify-center gap-1 rounded-full border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 px-2 py-1 text-sm font-medium text-gray-600 dark:text-gray-300"
                                title="{{ $reaction }}"
                            >
                                <span>{{ $reaction }}</span>
                                <span>{{ $data['count'] }}</span>
                            </span>
                        @endif
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    @if ($comment->isComment() && $comment->canDelete())
        <x-filament::modal
            id="delete-comment-modal-{{ $comment->getId() }}"
            wire:model="showDeleteModal"
            width="sm"
        >
            <x-slot name="heading">
                Delete Comment
            </x-slot>

            <div class="py-4">
                Are you sure you want to delete this comment? This action cannot be undone.
            </div>

            <x-slot name="footer">
                <div class="flex justify-end gap-x-4">
                    <x-filament::button
                        wire:click="$dispatch('close-modal', { id: 'delete-comment-modal-{{ $comment->getId() }}' })"
                        color="gray"
                    >
                        Cancel
                    </x-filament::button>

                    <x-filament::button
                        wire:click="delete"
                        color="danger"
                    >
                        Delete
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>
    @endif
</div>
