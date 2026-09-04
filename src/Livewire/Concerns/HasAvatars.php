<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Kirschbaum\Commentions\Config;

trait HasAvatars
{
    public ?bool $avatarsEnabled = null;

    public function avatarsAreEnabled(): bool
    {
        return $this->avatarsEnabled ?? Config::avatarsAreEnabled();
    }
}
