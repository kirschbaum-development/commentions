<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Concerns\HasMentionables;
use Kirschbaum\Commentions\Filament\Concerns\HasPolling;
use Kirschbaum\Commentions\Filament\Concerns\HasRatings;
use Kirschbaum\Commentions\Filament\Concerns\HasSidebar;
use Kirschbaum\Commentions\Filament\Concerns\HasTipTapCssClasses;

/**
 * Table/record action for the comments modal.
 *
 * Filament 3 keeps a dedicated table-action class (`Filament\Tables\Actions\Action`);
 * Filament 4/5 unified actions under `Filament\Actions\Action`. {@see TableAction}
 * resolves to the correct base class for the installed Filament version.
 */
class CommentsTableAction extends TableAction
{
    use HasMentionables;
    use HasPolling;
    use HasRatings;
    use HasSidebar;
    use HasTipTapCssClasses;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->icon('heroicon-o-chat-bubble-left-right')
            ->modalContent(fn (Model $record) => view('commentions::comments-modal', [
                'record' => $record,
                'mentionables' => $this->getMentionables(),
                'pollingInterval' => $this->getPollingInterval(),
                'sidebarEnabled' => $this->isSidebarEnabled(),
                'showSubscribers' => $this->showSubscribers(),
                'tipTapCssClasses' => $this->getTipTapCssClasses(),
                'ratingsEnabled' => $this->ratingsAreEnabled(),
                'maxRating' => $this->getMaxRating(),
            ]))
            ->modalWidth($this->isSidebarEnabled() ? '4xl' : 'xl')
            ->label(__('commentions::comments.label'))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalAutofocus(false);
    }

    public static function getDefaultName(): ?string
    {
        return 'commentList';
    }
}
