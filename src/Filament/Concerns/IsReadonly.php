<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

trait IsReadonly
{
    protected bool $readonly = false;

    public function readonly(bool $readonly = true): static
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }
}
