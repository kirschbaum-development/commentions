<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;
use Kirschbaum\Commentions\Config;

trait HasReactions
{
    protected bool|Closure|null $reactionsEnabled = null;

    public function enableReactions(bool|Closure $condition = true): static
    {
        $this->reactionsEnabled = $condition;

        return $this;
    }

    public function disableReactions(): static
    {
        $this->reactionsEnabled = false;

        return $this;
    }

    public function reactionsAreEnabled(): bool
    {
        $value = $this->evaluate($this->reactionsEnabled);

        return $value ?? Config::reactionsAreEnabled();
    }
}
