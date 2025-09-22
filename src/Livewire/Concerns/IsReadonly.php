<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

trait IsReadonly
{
    public bool $readonly = false;

    public function isReadonly(): bool
    {
        return $this->readonly;
    }
}
