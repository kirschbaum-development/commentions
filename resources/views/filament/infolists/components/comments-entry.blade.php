<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <livewire:commentions::comments
        :record="$getRecord()"
        :mentionables="$getMentionables()"
        :polling-interval="$getPollingInterval()"
    />
</x-dynamic-component>
