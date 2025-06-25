<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;

/**
 * @deprecated to be removed when dropping support for Filament 3.
 *
 * Filament 4 has expanded the `CanPoll` trait which is included in Components
 * by default in such a way that it provides everything this trait needs to do.
 * This trait is replicating the new functionality of `CanPoll` so that we can
 * maintain compatibility with Filament 3.
 */
trait HasPolling
{
    protected string|Closure|null $pollingInterval = null;

    /**
     * @deprecated Use `poll` with a string instead, e.g. `poll('10s')`
     */
    public function pollingInterval(int $interval = 60): static
    {
        $this->pollingInterval = $interval;

        return $this;
    }

    public function poll(string|Closure|null $interval = '60s'): static
    {
        $this->pollingInterval = $interval;

        return $this;
    }

    public function getPollingInterval(): ?string
    {
        return $this->evaluate($this->pollingInterval);
    }
}
