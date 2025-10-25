<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;
use Kirschbaum\Commentions\Config;

trait HasTipTapCssClasses
{
    protected string | Closure | null $tipTapCssClasses = null;

    public function tipTapCssClasses(string | Closure | null $classes): static
    {
        $this->tipTapCssClasses = $classes;

        return $this;
    }

    public function getTipTapCssClasses(): ?string
    {
        return $this->evaluate($this->tipTapCssClasses) ?? Config::getTipTapCssClasses();
    }
}
