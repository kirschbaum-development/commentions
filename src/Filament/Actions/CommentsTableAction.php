<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Concerns\HasMentionables;
use Kirschbaum\Commentions\Filament\Concerns\HasPolling;
use Kirschbaum\Commentions\Filament\Concerns\HasSidebar;
use Kirschbaum\Commentions\Filament\Concerns\HasTipTapCssClasses;
use Kirschbaum\Commentions\Filament\Concerns\HasToolbar;

class CommentsTableAction extends Action
{
    use HasMentionables;
    use HasPolling;
    use HasSidebar;
    use HasTipTapCssClasses;
    use HasToolbar;

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
                'toolbarButtons' => $this->getToolbarButtons(),
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
