<?php

namespace Kirschbaum\Commentions\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Kirschbaum\Commentions\Filament\Concerns\HasAttachments;
use Kirschbaum\Commentions\Filament\Concerns\HasMentionables;
use Kirschbaum\Commentions\Filament\Concerns\HasPagination;
use Kirschbaum\Commentions\Filament\Concerns\HasPolling;
use Kirschbaum\Commentions\Filament\Concerns\HasRatings;
use Kirschbaum\Commentions\Filament\Concerns\HasSidebar;
use Kirschbaum\Commentions\Filament\Concerns\HasTipTapCssClasses;
use Kirschbaum\Commentions\Filament\Concerns\HasToolbar;

class CommentsEntry extends Entry
{
    use HasAttachments;
    use HasMentionables;
    use HasPagination;
    use HasPolling;
    use HasRatings;
    use HasSidebar;
    use HasTipTapCssClasses;
    use HasToolbar;

    protected string $view = 'commentions::filament.infolists.components.comments-entry';
}
