<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <livewire:commentions::comments
        :record="$getRecord()"
        :mentionables="$getMentionables()"
        :polling-interval="$getPollingInterval()"
        :paginate="$isPaginated()"
        :per-page="$getPerPage()"
        :load-more-label="$getLoadMoreLabel()"
        :per-page-increment="$getPerPageIncrement()"
        :sidebar-enabled="$isSidebarEnabled()"
    />
</x-dynamic-component>
