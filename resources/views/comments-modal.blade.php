<div>
    <livewire:commentions::comments
        :key="'comments-modal'"
        :record="$record"
        :mentionables="$mentionables"
        :polling-interval="$pollingInterval"
        :paginate="$paginate ?? true"
        :per-page="$perPage ?? 5"
        :load-more-label="$loadMoreLabel ?? __('commentions::comments.show_more')"
        :per-page-increment="$perPageIncrement ?? null"
        :sidebar-enabled="$sidebarEnabled ?? true"
        :show-subscribers="$showSubscribers ?? true"
    />
</div>
