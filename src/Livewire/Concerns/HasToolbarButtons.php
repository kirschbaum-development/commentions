<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Kirschbaum\Commentions\Config;

trait HasToolbarButtons
{
    /** @var array<int, array<int, string>>|null */
    public ?array $toolbarButtons = null;

    /**
     * The editor toolbar buttons, normalized into groups, and falling back to
     * the package configuration when not explicitly provided by the host
     * component. Normalizing here means a flat list passed straight to the
     * component renders the same as a grouped one.
     *
     * @return array<int, array<int, string>>
     */
    public function getToolbarButtons(): array
    {
        if ($this->toolbarButtons === null) {
            return Config::getToolbarButtons();
        }

        return Config::normalizeToolbarButtons($this->toolbarButtons);
    }
}
