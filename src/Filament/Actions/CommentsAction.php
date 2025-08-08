<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Concerns\HasMentionables;
use Kirschbaum\Commentions\Filament\Concerns\HasPagination;
use Kirschbaum\Commentions\Filament\Concerns\HasPolling;

class CommentsAction extends Action
{
    use HasMentionables;
    use HasPagination;
    use HasPolling;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->icon('heroicon-o-chat-bubble-left-right')
            ->modalContent(fn (Model $record) => view('commentions::comments-modal', [
                'record' => $record,
                'mentionables' => $this->getMentionables(),
                'pollingInterval' => $this->getPollingInterval(),
                'paginate' => $this->isPaginated(),
                'perPage' => $this->getPerPage(),
                'loadMoreLabel' => $this->getLoadMoreLabel(),
                'perPageIncrement' => $this->getPerPageIncrement() ?: $this->getPerPage(),
            ]))
            ->modalWidth('xl')
            ->label('Comments')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalAutofocus(false);
    }

    public static function getDefaultName(): ?string
    {
        return 'comments';
    }
}
