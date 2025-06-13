<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Concerns\HasMentionables;
use Kirschbaum\Commentions\Filament\Concerns\HasPolling;

class CommentsTableAction extends Action
{
    use HasMentionables;
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
            ]))
            ->modalWidth('xl')
            ->label('Comments')
            ->modalAutofocus(false);
    }

    public static function getDefaultName(): ?string
    {
        return 'commentList';
    }
}
