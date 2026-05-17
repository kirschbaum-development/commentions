<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <livewire:dynamic-component
        :component="$commentionsComponentPrefix . 'comments'"
        :record="$getRecord()"
        :mentionables="$getMentionables()"
        :polling-interval="$getPollingInterval()"
        :paginate="$isPaginated()"
        :per-page="$getPerPage()"
        :load-more-label="$getLoadMoreLabel()"
        :per-page-increment="$getPerPageIncrement()"
        :sidebar-enabled="$isSidebarEnabled()"
        :tip-tap-css-classes="$getTipTapCssClasses()"
        :ratings-enabled="$ratingsAreEnabled()"
        :max-rating="$getMaxRating()"
    />
</x-dynamic-component>
