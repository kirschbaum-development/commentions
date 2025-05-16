<div class="comm:relative comm:mt-2 comm:pt-2 comm:border-t comm:border-gray-200 comm:dark:border-gray-700 comm:flex comm:items-center comm:gap-x-1 comm:flex-wrap">
    {{-- Inline buttons for existing reactions --}}
    @foreach ($this->reactionSummary as $reactionData)
        <span wire:key="inline-reaction-button-{{ $reactionData['reaction'] }}-{{ $comment->getId() }}">
            <button
                x-cloak
                wire:click="handleReactionToggle('{{ $reactionData['reaction'] }}')"
                type="button"
                class="comm:inline-flex comm:items-center comm:justify-center comm:gap-1 comm:rounded-full comm:border comm:px-2 comm:h-8 comm:text-xs comm:font-medium comm:transition comm:focus:outline-none comm:focus:ring-2 comm:focus:ring-offset-2 comm:disabled:opacity-50 comm:disabled:cursor-not-allowed
                    {{ $reactionData['reacted_by_current_user']
                        ? 'comm:bg-gray-50 comm:dark:bg-gray-800 comm:border-gray-300 comm:dark:border-gray-600 comm:text-gray-700 comm:dark:text-gray-200 comm:hover:bg-gray-200 comm:dark:hover:bg-gray-600'
                        : 'comm:bg-white comm:dark:bg-gray-900 comm:border-gray-300 comm:dark:border-gray-600 comm:text-gray-700 comm:dark:text-gray-200 comm:hover:bg-gray-100 comm:dark:hover:bg-gray-600' }}"
                title="{{ $reactionData['reaction'] }}"

            >
                <span>{{ $reactionData['reaction'] }}</span>
                <span wire:key="inline-reaction-count-{{ $reactionData['reaction'] }}-{{ $comment->getId() }}">{{ $reactionData['count'] }}</span>
            </button>
        </span>
    @endforeach

    {{-- Add Reaction Button --}}
    <div class="comm:relative" x-data="{ open: false }" wire:ignore.self>
        <button
            x-on:click="open = !open"
            type="button"
            @disabled(! auth()->check())
            class="comm:inline-flex comm:items-center comm:justify-center comm:gap-1 comm:rounded-full comm:border comm:border-gray-300 comm:dark:border-gray-600 comm:bg-white comm:dark:bg-gray-900 comm:w-8 comm:h-8 comm:text-xs comm:font-medium comm:text-gray-700 comm:dark:text-gray-200 comm:transition comm:hover:bg-gray-100 comm:dark:hover:bg-gray-700 comm:focus:outline-none comm:focus:ring-2 comm:focus:ring-offset-2 comm:disabled:opacity-50 comm:disabled:cursor-not-allowed"
            title="Add Reaction"
            wire:key="add-reaction-button-{{ $comment->getId() }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="comm:h-4 comm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>

        {{-- Reaction Popup --}}
        <div
            x-show="open"
            x-cloak
            x-on:click.away="open = false"
            class="comm:absolute comm:bottom-full comm:mb-2 comm:z-10 comm:bg-white comm:dark:bg-gray-800 comm:border comm:border-gray-300 comm:dark:border-gray-600 comm:rounded-lg comm:shadow-lg comm:p-2 comm:flex-wrap comm:gap-1 comm:w-max comm:max-w-xs"
            :class="{ 'comm:hidden': ! open, 'comm:flex': open }"
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
                    class="comm:inline-flex comm:items-center comm:justify-center comm:gap-1 comm:rounded-full comm:w-8 comm:h-8 comm:text-xs comm:font-medium comm:transition comm:focus:outline-none comm:focus:ring-2 comm:focus:ring-offset-2 comm:disabled:opacity-50 comm:disabled:cursor-not-allowed
                           {{ $reactionData['reacted_by_current_user']
                               ? 'comm:text-gray-700 comm:dark:text-gray-200 comm:hover:bg-gray-200 comm:dark:hover:bg-gray-600'
                               : 'comm:text-gray-700 comm:dark:text-gray-200 comm:hover:bg-gray-100 comm:dark:hover:bg-gray-600' }}"
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
                class="comm:inline-flex comm:items-center comm:justify-center comm:gap-1 comm:rounded-full comm:border comm:border-gray-300 comm:dark:border-gray-600 comm:bg-gray-100 comm:dark:bg-gray-800 comm:px-2 comm:h-8 comm:text-xs comm:font-medium comm:text-gray-600 comm:dark:text-gray-300"
                title="{{ $reactionEmoji }}"
            >
                <span>{{ $reactionEmoji }}</span>
                <span>{{ $data['count'] }}</span>
            </span>
        @endif
    @endforeach
</div>
