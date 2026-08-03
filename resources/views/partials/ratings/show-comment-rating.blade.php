@if($comment->isComment() && $comment->rating)
    <div
        class="comm:mt-1 comm:flex comm:items-center comm:gap-0.5"
        role="img"
        title="{{ $comment->rating }}/{{ $maxRating }}"
        aria-label="{{ __('commentions::comments.rating_display_label', ['rating' => $comment->rating, 'max' => $maxRating]) }}"
    >
        @for ($ratingStar = 1; $ratingStar <= $maxRating; $ratingStar++)
            <x-filament::icon
                icon="heroicon-s-star"
                @class([
                    'comm:h-4 comm:w-4',
                    'comm:text-amber-400' => $ratingStar <= $comment->rating,
                    'comm:text-gray-300 comm:dark:text-gray-600' => $ratingStar > $comment->rating,
                ])
            />
        @endfor
    </div>
@endif
