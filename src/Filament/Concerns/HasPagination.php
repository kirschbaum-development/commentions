<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;

trait HasPagination
{
    protected bool|Closure $paginate = true;

    protected int|Closure $perPage = 5;

    protected string|Closure|null $loadMoreLabel = null;

    protected int|Closure|null $perPageIncrement = null;

    public function paginate(bool|Closure $enabled = true): static
    {
        $this->paginate = $enabled;

        return $this;
    }

    public function disablePagination(): static
    {
        return $this->paginate(false);
    }

    public function perPage(int|Closure $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function loadMoreLabel(string|Closure $label): static
    {
        $this->loadMoreLabel = $label;

        return $this;
    }

    public function loadMoreIncrementsBy(int|Closure $increment): static
    {
        $this->perPageIncrement = $increment;

        return $this;
    }

    public function isPaginated(): bool
    {
        return (bool) $this->evaluate($this->paginate);
    }

    public function getPerPage(): int
    {
        return (int) $this->evaluate($this->perPage);
    }

    public function getLoadMoreLabel(): string
    {
        return (string) filled($this->evaluate($this->loadMoreLabel)) ? $this->evaluate($this->loadMoreLabel) : __('commentions::comments.show_more');
    }

    public function getPerPageIncrement(): int
    {
        $value = $this->evaluate($this->perPageIncrement);

        return (int) ($value ?? $this->getPerPage());
    }
}
