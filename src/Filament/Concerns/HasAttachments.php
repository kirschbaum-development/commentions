<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;

trait HasAttachments
{
    protected bool|Closure|null $attachmentsEnabled = null;

    public function enableAttachments(bool|Closure $condition = true): static
    {
        $this->attachmentsEnabled = $condition;

        return $this;
    }

    public function disableAttachments(): static
    {
        $this->attachmentsEnabled = false;

        return $this;
    }

    public function attachmentsAreEnabled(): bool
    {
        $value = $this->evaluate($this->attachmentsEnabled);

        return $value ?? (bool) config('commentions.attachments.enabled', false);
    }
}
