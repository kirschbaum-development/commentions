<div @if ($pollingInterval) wire:poll.{{ $pollingInterval }} @endif>
    @if ($this->comments->isEmpty())
        <div class="comm:flex comm:items-center comm:justify-center comm:p-6 comm:text-center comm:rounded-lg comm:border comm:border-dashed comm:border-gray-300 comm:dark:border-gray-700">
            <div class="comm:flex comm:flex-col comm:items-center comm:gap-y-2">
                <x-filament::icon
                    icon="heroicon-o-chat-bubble-left-right"
                    class="comm:w-8 comm:h-8 comm:text-gray-400 comm:dark:text-gray-500"
                />

                <span class="comm:text-sm comm:font-medium comm:text-gray-500 comm:dark:text-gray-400">
                    No comments yet.
                </span>
            </div>
        </div>
    @endif

    @foreach ($this->comments as $comment)
        <livewire:commentions::comment
            :key="$comment->getContentHash()"
            :comment="$comment"
            :mentionables="$mentionables"
        />
    @endforeach
</div>
