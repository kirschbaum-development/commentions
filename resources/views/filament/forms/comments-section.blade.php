<div class="p-6 bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('commentions::comments.label') }}</h3>
    
    @if($record ?? null)
        @livewire('commentions::comments', [
            'record' => $record,
            'mentionables' => config('commentions.mentionables.default', \App\Models\User::all())
        ])
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('commentions::comments.save_record_first', ['default' => 'Save the record first to enable comments.']) }}</p>
    @endif
</div>
