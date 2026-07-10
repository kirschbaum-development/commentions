<div
    class="comm:mb-2 comm:flex comm:items-center comm:gap-0.5"
    x-data="{ hover: 0 }"
    role="group"
    aria-label="{{ __('commentions::comments.rating_input_label') }}"
>
    @for ($ratingStar = 1; $ratingStar <= $maxRating; $ratingStar++)
        <button
            type="button"
            wire:key="comment-rating-star-{{ $ratingStar }}"
            x-on:click="$wire.rating = ($wire.rating === {{ $ratingStar }} ? null : {{ $ratingStar }})"
            x-on:mouseenter="hover = {{ $ratingStar }}"
            x-on:mouseleave="hover = 0"
            class="commentions-rating-star"
            :class="{ 'commentions-rating-star-active': (hover || $wire.rating) >= {{ $ratingStar }} }"
            :aria-pressed="($wire.rating === {{ $ratingStar }}).toString()"
            aria-label="{{ trans_choice('commentions::comments.rate_stars', $ratingStar, ['count' => $ratingStar]) }}"
        >
            <x-filament::icon icon="heroicon-s-star" class="comm:h-5 comm:w-5" />
        </button>
    @endfor
</div>

@error('rating')
    <p class="comm:mb-2 comm:text-xs comm:text-red-600">{{ $message }}</p>
@enderror
