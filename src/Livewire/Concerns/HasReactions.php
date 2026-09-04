<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Kirschbaum\Commentions\Config;

trait HasReactions
{
    public ?bool $reactionsEnabled = null;

    public function reactionsAreEnabled(): bool
    {
        return $this->reactionsEnabled ?? Config::reactionsAreEnabled();
    }
}
