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
     * Override the record used for comments, independent from the page's record.
     *
     * Useful when rendering comments inside a table modal or any context where
     * the target commentable differs from the container record (e.g. a pivot).
     *
     * Proxies to the underlying Filament model/record handling so getRecord()
     * (used by the entry blade view) resolves to the given record.
     *
     * @param  Model|array<string, mixed>|Closure|null  $record
     */
    public function record(Model|array|Closure|null $record): static
    {
        $this->model($record);

        return $this;
    }
}
