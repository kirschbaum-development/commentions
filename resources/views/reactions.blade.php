<div class="relative mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center gap-x-1 flex-wrap">
    {{-- Inline buttons for existing reactions --}}
    @foreach ($this->reactionSummary as $reactionData)
        <span wire:key="inline-reaction-button-{{ $reactionData['reaction'] }}-{{ $comment->getId() }}">
            <button
                x-cloak
                wire:click="handleReactionToggle('{{ $reactionData['reaction'] }}')"
                type="button"
                class="inline-flex items-center justify-center gap-1 rounded-full border px-2 h-8 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed
                    {{ $reactionData['reacted_by_current_user']
                        ? 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600'
                        : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600' }}"
                title="{{ $reactionData['reaction'] }}"

            >
                <span>{{ $reactionData['reaction'] }}</span>
                <span wire:key="inline-reaction-count-{{ $reactionData['reaction'] }}-{{ $comment->getId() }}">{{ $reactionData['count'] }}</span>
            </button>
        </span>
    @endforeach

    {{-- Add Reaction Button --}}
    <div class="relative" x-data="{ open: false }" wire:ignore.self>
        <button
            x-on:click="open = !open"
            type="button"
            @disabled(! auth()->check())
            class="inline-flex items-center justify-center gap-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-2 h-8 text-sm font-medium text-gray-700 dark:text-gray-200 transition hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            title="Add Reaction"
            wire:key="add-reaction-button-{{ $comment->getId() }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>

        {{-- Reaction Popup --}}
        <div
            x-show="open"
            x-cloak
            x-on:click.away="open = false"
            class="absolute bottom-full mb-2 z-10 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-2 flex flex-wrap gap-1 w-max max-w-xs"
        >
            @foreach ($allowedReactions as $reactionEmoji)
                @php
                    $reactionData = $this->reactionSummary[$reactionEmoji] ?? ['count' => 0, 'reacted_by_current_user' => false];
                @endphp

                <button
                    wire:click="handleReactionToggle('{{ $reactionEmoji }}')"
                    x-on:click="open = false"
                    type="button"
                    @disabled(! auth()->check())
                    class="inline-flex items-center justify-center gap-1 rounded-full border px-2 h-8 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed
                           {{ $reactionData['reacted_by_current_user']
                               ? 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600'
                               : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600' }}"
                    title="{{ $reactionEmoji }}"
                    wire:key="popup-reaction-button-{{ $reactionEmoji }}-{{ $comment->getId() }}"
                >
                    <span>{{ $reactionEmoji }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Display summary of reactions not explicitly in the allowed list --}}
    @foreach ($this->reactionSummary as $reactionEmoji => $data)
        @if (! in_array($reactionEmoji, $allowedReactions) && $data['count'] > 0)
            <span
                wire:key="reaction-extra-{{ $reactionEmoji }}-{{ $comment->getId() }}"
                class="inline-flex items-center justify-center gap-1 rounded-full border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 px-2 h-8 text-sm font-medium text-gray-600 dark:text-gray-300"
                title="{{ $reactionEmoji }}"
            >
                <span>{{ $reactionEmoji }}</span>
                <span>{{ $data['count'] }}</span>
            </span>
        @endif
    @endforeach
</div>
