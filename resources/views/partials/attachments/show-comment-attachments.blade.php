@if($comment->isComment() && $comment->attachments)
    <div class="comm:mt-2 comm:flex comm:flex-wrap comm:gap-2">
        @foreach ($comment->attachments as $attachment)
            @if ($attachment->isImage())
                <a href="{{ $attachment->getUrl() }}" target="_blank" rel="noopener">
                    <img
                        src="{{ $attachment->getUrl() }}"
                        alt="{{ $attachment->filename }}"
                        class="comm:h-20 comm:w-20 comm:rounded-lg comm:border comm:border-gray-200 comm:dark:border-gray-700 comm:object-cover"
                    />
                </a>
            @else
                <a
                    href="{{ $attachment->getUrl() }}"
                    target="_blank"
                    rel="noopener"
                    class="comm:inline-flex comm:items-center comm:gap-1 comm:rounded-lg comm:border comm:border-gray-200 comm:dark:border-gray-700 comm:px-2 comm:py-1 comm:text-xs comm:text-gray-700 comm:dark:text-gray-200 comm:hover:bg-gray-100 comm:dark:hover:bg-gray-800"
                >
                    <x-filament::icon icon="heroicon-s-paper-clip" class="comm:h-4 comm:w-4" />
                    <span class="comm:max-w-[12rem] comm:truncate">{{ $attachment->filename }}</span>
                </a>
            @endif
        @endforeach
    </div>
@endif
