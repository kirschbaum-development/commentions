@php
    $mimeTypes = config('commentions.attachments.accepted_mime_types');
    $hasMimeTypes = is_array($mimeTypes) && $mimeTypes !== [];
@endphp

<div class="comm:mb-2 comm:space-y-1" x-show="wasFocused" x-cloak>
    <label class="comm:inline-flex comm:cursor-pointer comm:items-center comm:gap-1 comm:rounded-lg comm:border comm:border-gray-300 comm:dark:border-gray-700 comm:px-2 comm:py-1 comm:text-xs comm:text-gray-600 comm:dark:text-gray-300 comm:hover:bg-gray-100 comm:dark:hover:bg-gray-800">
        <input type="file"
               class="comm:hidden"
               wire:model="attachments"
               @if(config('commentions.attachments.max_files') > 1)
                    multiple
               @endif
               @if($hasMimeTypes)
                   accept="{{ implode(',', $mimeTypes) }}"
               @endif
        />
        <x-filament::icon icon="heroicon-s-paper-clip" class="comm:h-4 comm:w-4" />
        <span>{{ __('commentions::comments.attach_files') }}</span>
    </label>

    <div wire:loading wire:target="attachments" class="comm:text-xs comm:text-gray-500">
        {{ __('commentions::comments.uploading') }}
    </div>

    @if ($errors->has('attachments') || ! empty($attachments))
        <div class="comm:space-y-3 comm:py-3">
            @error('attachments')
            <p class="comm:text-xs commentions-error">{{ $message }}</p>
            @enderror

            @if (! empty($attachments))
                <ul class="comm:space-y-3">
                    @foreach ($attachments as $attachmentIndex => $pendingAttachment)
                        @php
                            $attachmentError = $errors->first("attachments.$attachmentIndex");
                        @endphp

                        <li class="comm:flex comm:items-center comm:gap-1.5 comm:text-xs comm:text-gray-600 comm:dark:text-gray-300">
                            @if ($attachmentError)
                                <span
                                    x-data
                                    x-tooltip.raw="{{ $attachmentError }}"
                                    class="commentions-error"
                                >
                                    <x-filament::icon
                                        icon="heroicon-s-x-circle"
                                        class="comm:h-5 comm:w-5"
                                    />
                                </span>
                            @else
                                <x-filament::icon
                                    icon="heroicon-s-paper-clip"
                                    class="comm:h-4 comm:w-4 comm:flex-shrink-0"
                                />
                            @endif

                            <span class="comm:truncate">
                                {{ $pendingAttachment->getClientOriginalName() }}
                            </span>

                            <button
                                type="button"
                                wire:click="removeAttachment({{ $attachmentIndex }})"
                                class="commentions-error"
                            >
                                <x-filament::icon icon="heroicon-s-trash" class="comm:h-5 comm:w-5" />
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
