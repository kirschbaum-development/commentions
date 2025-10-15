<?php

namespace Kirschbaum\Commentions\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Kirschbaum\Commentions\Filament\Concerns\HasMentionables;
use Kirschbaum\Commentions\Filament\Concerns\HasPagination;
use Kirschbaum\Commentions\Filament\Concerns\HasPolling;
use Kirschbaum\Commentions\Filament\Concerns\HasSidebar;

class CommentsEntry extends Entry
{
    use HasMentionables;
    use HasPagination;
    use HasPolling;
    use HasSidebar;

    protected string $view = 'commentions::filament.infolists.components.comments-entry';
}
