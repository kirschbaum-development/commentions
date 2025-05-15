<div @if ($pollingInterval) wire:poll.{{ $pollingInterval }}s @endif>
    @if ($this->comments->isEmpty())
        <div class="flex items-center justify-center p-6 text-center rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
            <div class="flex flex-col items-center gap-y-2">
                <x-filament::icon
                    icon="heroicon-o-chat-bubble-left-right"
                    class="w-8 h-8 text-gray-400 dark:text-gray-500"
                />

                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
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
