<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Livewire\Attributes\Computed;

trait HasPagination
{
    public bool $paginate = true;

    public int $perPage = 5;

    public ?string $loadMoreLabel = null;

    public ?int $perPageIncrement = null;

    public function loadMore(): void
    {
        $increment = $this->perPageIncrement ?? $this->perPage;
        $this->perPage += $increment;

        if (property_exists($this, 'comments')) {
            unset($this->comments);
        }
    }

    #[Computed]
    public function hasMore(): bool
    {
        if (! $this->paginate) {
            return false;
        }

        if (! property_exists($this, 'record') || $this->record === null) {
            return false;
        }

        return $this->record->comments()->count() > $this->perPage;
    }

    public function getLoadMoreLabel(): string
    {
        return __('commentions::comments.show_more');
    }
}
