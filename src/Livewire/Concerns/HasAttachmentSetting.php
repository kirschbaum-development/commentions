<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Livewire\Attributes\Locked;

/**
 * The resolved "attachments enabled?" flag, propagated from the Filament layer
 * down the Livewire component tree. Components that only read the setting use
 * this; those that accept uploads use the richer {@see HasAttachments} trait.
 */
trait HasAttachmentSetting
{
    // #[Locked] so a client can't enable a closure-gated setting by tampering with the payload.
    #[Locked]
    public ?bool $attachmentsEnabled = null;

    public function attachmentsAreEnabled(): bool
    {
        return $this->attachmentsEnabled ?? (bool) config('commentions.attachments.enabled', false);
    }
}
