<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Kirschbaum\Commentions\Config;

trait HasRatings
{
    public ?bool $ratingsEnabled = null;

    public ?int $maxRating = null;

    public function ratingsAreEnabled(): bool
    {
        return $this->ratingsEnabled ?? Config::ratingsAreEnabled();
    }

    public function getMaxRating(): int
    {
        return $this->maxRating ?? Config::getMaxRating();
    }
}
