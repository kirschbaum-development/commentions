@php
    $toolbarButtonIcons = [
        'bold' => 'heroicon-s-bold',
        'italic' => 'heroicon-s-italic',
        'underline' => 'heroicon-s-underline',
        'strike' => 'heroicon-s-strikethrough',
        'h1' => 'heroicon-s-h1',
        'h2' => 'heroicon-s-h2',
        'h3' => 'heroicon-s-h3',
        'blockquote' => 'heroicon-s-chat-bubble-left-ellipsis',
        'bulletList' => 'heroicon-s-list-bullet',
        'orderedList' => 'heroicon-s-numbered-list',
        'code' => 'heroicon-s-code-bracket',
        'link' => 'heroicon-s-link',
    ];

    $toolbarButtonLabels = [
        'bold' => __('commentions::comments.toolbar.bold'),
        'italic' => __('commentions::comments.toolbar.italic'),
        'underline' => __('commentions::comments.toolbar.underline'),
        'strike' => __('commentions::comments.toolbar.strike'),
        'h1' => __('commentions::comments.toolbar.h1'),
        'h2' => __('commentions::comments.toolbar.h2'),
        'h3' => __('commentions::comments.toolbar.h3'),
        'blockquote' => __('commentions::comments.toolbar.blockquote'),
        'bulletList' => __('commentions::comments.toolbar.bullet_list'),
        'orderedList' => __('commentions::comments.toolbar.ordered_list'),
        'code' => __('commentions::comments.toolbar.code'),
        'link' => __('commentions::comments.toolbar.link'),
    ];
@endphp

@if (! empty($toolbarButtons))
    <div class="commentions-toolbar" role="toolbar" aria-label="{{ __('commentions::comments.toolbar.aria_label') }}">
        @foreach ($toolbarButtons as $toolbarGroup)
            @php
                $renderableButtons = array_filter(
                    $toolbarGroup,
                    fn ($button) => isset($toolbarButtonIcons[$button]),
                );
            @endphp

            @if (! empty($renderableButtons))
                <div class="commentions-toolbar-group">
                    @foreach ($renderableButtons as $toolbarButton)
                        <button
                            type="button"
                            data-toolbar-button="{{ $toolbarButton }}"
                            class="commentions-toolbar-button"
                            aria-label="{{ $toolbarButtonLabels[$toolbarButton] ?? $toolbarButton }}"
                            title="{{ $toolbarButtonLabels[$toolbarButton] ?? $toolbarButton }}"
                            aria-pressed="false"
                            :class="{ 'commentions-toolbar-button-active': isToolbarButtonActive('{{ $toolbarButton }}') }"
                            :aria-pressed="isToolbarButtonActive('{{ $toolbarButton }}') ? 'true' : 'false'"
                            x-on:click="runToolbarCommand('{{ $toolbarButton }}')"
                        >
                            <x-filament::icon :icon="$toolbarButtonIcons[$toolbarButton]" class="comm:h-4 comm:w-4" />
                        </button>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
@endif
