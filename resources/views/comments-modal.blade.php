<div>
    <livewire:commentions::comments
        :key="'comments-modal'"
        :record="$record"
        :mentionables="$mentionables"
        :polling-interval="$pollingInterval"
        :paginate="$paginate ?? true"
        :per-page="$perPage ?? 5"
        :load-more-label="$loadMoreLabel ?? 'Show more'"
        :per-page-increment="$perPageIncrement ?? null"
    />
</div>
