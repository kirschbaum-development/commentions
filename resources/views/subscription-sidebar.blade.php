<div class="comm:w-48 comm:flex-shrink-0 comm:pl-2 comm:ml-2">
    <div class="comm:sticky comm:top-4">
        <div class="comm:bg-gray-50 comm:dark:bg-gray-800 comm:rounded-lg comm:p-4 comm:border comm:border-gray-200 comm:dark:border-gray-700">
            <div class="comm:flex comm:items-center comm:gap-2 comm:mb-3">
                <x-filament::icon
                    icon="heroicon-o-bell"
                    class="comm:w-4 comm:h-4 comm:text-gray-700 comm:dark:text-gray-300"
                />
                <h3 class="comm:text-sm comm:font-bold comm:text-gray-900 comm:dark:text-gray-100">
                    {{ __('commentions::comments.notifications') }}
                </h3>
            </div>

            @if ($this->isSubscribed)
                <x-filament::button
                    wire:click="toggleSubscription"
                    wire:target="toggleSubscription"
                    wire:loading.attr="disabled"
                    color="gray"
                    size="xs"
                    class="comm:w-full comm:mb-2 comm:inline-flex comm:items-center comm:whitespace-nowrap"
                >
                    <span class="comm:inline-flex comm:items-center comm:gap-1 comm:whitespace-nowrap">
                        <x-filament::icon
                            icon="heroicon-s-bell-slash"
                            class="comm:w-3 comm:h-3 comm:flex-shrink-0"
                        />
                        <span>{{ __('commentions::comments.unsubscribe') }}</span>
                    </span>
                </x-filament::button>
            @else
                <x-filament::button
                    wire:click="toggleSubscription"
                    wire:target="toggleSubscription"
                    wire:loading.attr="disabled"
                    color="gray"
                    size="xs"
                    class="comm:w-full comm:mb-2 comm:inline-flex comm:items-center comm:whitespace-nowrap"
                >
                    <span class="comm:inline-flex comm:items-center comm:gap-1 comm:whitespace-nowrap">
                        <x-filament::icon
                            icon="heroicon-o-bell"
                            class="comm:w-3 comm:h-3 comm:flex-shrink-0"
                        />
                        <span>{{ __('commentions::comments.subscribe') }}</span>
                    </span>
                </x-filament::button>
            @endif

            {{-- Subscribers List --}}
            @if ($showSubscribers && $this->subscribers->isNotEmpty())
                <div class="comm:border-t comm:border-gray-200 comm:dark:border-gray-600 comm:pt-3">
                    <div class="comm:flex comm:items-center comm:gap-2 comm:mb-3">
                        <x-filament::icon
                            icon="heroicon-o-users"
                            class="comm:w-4 comm:h-4 comm:text-gray-700 comm:dark:text-gray-300"
                        />
                        <span class="comm:text-sm comm:font-bold comm:text-gray-900 comm:dark:text-gray-100">
                            {{ __('commentions::comments.subscribers') }} ({{ $this->subscribers->count() }})
                        </span>
                    </div>
                    <div class="comm:space-y-1">
                        @foreach ($this->subscribers->take(5) as $subscriber)
                            <div class="comm:flex comm:items-center comm:gap-2">
                                @if ($subscriber instanceof \Filament\Models\Contracts\HasAvatar && $subscriber->getFilamentAvatarUrl())
                                    <img
                                        src="{{ $subscriber->getFilamentAvatarUrl() }}"
                                        alt="{{ $subscriber->name }}"
                                        class="comm:w-4 comm:h-4 comm:rounded-full comm:object-cover comm:flex-shrink-0"
                                    />
                                @else
                                    <div class="comm:w-4 comm:h-4 comm:rounded-full comm:bg-gray-300 comm:dark:bg-gray-600 comm:flex-shrink-0 comm:flex comm:items-center comm:justify-center">
                                        <span class="comm:text-xs comm:font-medium comm:text-gray-600 comm:dark:text-gray-300">
                                            {{ substr($subscriber->name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                <span class="comm:text-xs comm:text-gray-600 comm:dark:text-gray-400 comm:truncate">
                                    {{ $subscriber->name }}
                                </span>
                            </div>
                        @endforeach
                        @if ($this->subscribers->count() > 5)
                            <div class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-400 comm:pl-6">
                                +{{ $this->subscribers->count() - 5 }} {{ trans_choice('commentions::comments.more', $this->subscribers->count() - 5) }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

