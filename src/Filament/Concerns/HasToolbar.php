<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;
use Kirschbaum\Commentions\Config;

trait HasToolbar
{
    /** @var array<mixed>|Closure|null */
    protected array|Closure|null $toolbarButtons = null;

    /**
     * Configure the editor formatting toolbar. Accepts a flat list of button
     * names, or groups of names for visual separators, e.g.
     * ->toolbarButtons([['bold', 'italic'], ['link']]).
     *
     * @param  array<mixed>|Closure|null  $buttons
     */
    public function toolbarButtons(array|Closure|null $buttons): static
    {
        $this->toolbarButtons = $buttons;

        return $this;
    }

    /**
     * @return array<int, array<int, string>>|null
     */
    public function getToolbarButtons(): ?array
    {
        $buttons = $this->evaluate($this->toolbarButtons);

        return $buttons === null
            ? null
            : Config::normalizeToolbarButtons($buttons);
    }
}
