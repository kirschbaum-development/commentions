<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;
use Kirschbaum\Commentions\Config;

trait HasRatings
{
    protected bool|Closure|null $ratingsEnabled = null;

    protected int|Closure|null $maxRating = null;

    public function enableRatings(bool|Closure $condition = true): static
    {
        $this->ratingsEnabled = $condition;

        return $this;
    }

    public function disableRatings(): static
    {
        $this->ratingsEnabled = false;

        return $this;
    }

    public function maxRating(int|Closure $max): static
    {
        $this->maxRating = $max;

        return $this;
    }

    public function ratingsAreEnabled(): bool
    {
        return $this->evaluate($this->ratingsEnabled) ?? Config::ratingsAreEnabled();
    }

    public function getMaxRating(): int
    {
        return (int) ($this->evaluate($this->maxRating) ?? Config::getMaxRating());
    }
}
