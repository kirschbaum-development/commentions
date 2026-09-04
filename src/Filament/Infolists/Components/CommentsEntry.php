<?php

namespace Kirschbaum\Commentions\Filament\Infolists\Components;

use Closure;
use Filament\Infolists\Components\Entry;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Concerns\HasAttachments;
use Kirschbaum\Commentions\Filament\Concerns\HasAvatars;
use Kirschbaum\Commentions\Filament\Concerns\HasMentionables;
use Kirschbaum\Commentions\Filament\Concerns\HasPagination;
use Kirschbaum\Commentions\Filament\Concerns\HasPolling;
use Kirschbaum\Commentions\Filament\Concerns\HasRatings;
use Kirschbaum\Commentions\Filament\Concerns\HasReactions;
use Kirschbaum\Commentions\Filament\Concerns\HasSidebar;
use Kirschbaum\Commentions\Filament\Concerns\HasTipTapCssClasses;
use Kirschbaum\Commentions\Filament\Concerns\HasToolbar;
use Kirschbaum\Commentions\Filament\Concerns\IsReadonly;

class CommentsEntry extends Entry
{
    use HasAttachments;
    use HasAvatars;
    use HasMentionables;
    use HasPagination;
    use HasPolling;
    use HasRatings;
    use HasReactions;
    use HasSidebar;
    use HasTipTapCssClasses;
    use HasToolbar;
    use IsReadonly;

    protected string $view = 'commentions::filament.infolists.components.comments-entry';

    /**
     * The record override set via `record()`, kept separate from Filament's
     * `$model` so that resolving it never re-enters record resolution.
     *
     * @var Model|array<string, mixed>|Closure|null
     */
    protected Model|array|Closure|null $commentionsRecord = null;

    protected bool $isResolvingCommentionsRecord = false;

    /**
     * Override the record used for comments, independent from the page's record.
     *
     * Useful when rendering comments inside a table modal or any context where
     * the target commentable differs from the container record (e.g. a pivot).
     *
     * Closures may type-hint or name a `$record` parameter, which receives the
     * container (page) record.
     *
     * @param  Model|array<string, mixed>|Closure|null  $record
     */
    public function record(Model|array|Closure|null $record): static
    {
        $this->commentionsRecord = $record;

        return $this;
    }

    /**
     * @return Model|array<string, mixed>|null
     */
    public function getRecord(bool $withContainerRecord = true): Model|array|null
    {
        // While resolving the override, fall back to the container record so a
        // `$record` parameter in the closure does not recurse back into here.
        if ($this->commentionsRecord === null || $this->isResolvingCommentionsRecord) {
            return parent::getRecord($withContainerRecord);
        }

        $this->isResolvingCommentionsRecord = true;

        try {
            return $this->evaluate($this->commentionsRecord);
        } finally {
            $this->isResolvingCommentionsRecord = false;
        }
    }
}
