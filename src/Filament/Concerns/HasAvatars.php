<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;
use Kirschbaum\Commentions\Config;

trait HasAvatars
{
    protected bool|Closure|null $avatarsEnabled = null;

    public function enableAvatars(bool|Closure $condition = true): static
    {
        $this->avatarsEnabled = $condition;

        return $this;
    }

    public function disableAvatars(): static
    {
        $this->avatarsEnabled = false;

        return $this;
    }

    public function avatarsAreEnabled(): bool
    {
        $value = $this->evaluate($this->avatarsEnabled);

        return $value ?? Config::avatarsAreEnabled();
    }
}
