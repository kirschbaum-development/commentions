<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Filament\Actions\Action;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Comment;

/**
 * Provides the built-in edit/delete Filament actions for a comment, and caches
 * any host-registered custom actions so they remain resolvable across requests.
 *
 * @mixin Comment
 */
trait HasCommentActions
{
    /** @var array<Action>|null */
    protected ?array $customActions = null;

    /**
     * Invoked by Filament's InteractsWithActions::cacheTraitActions() before
     * mounted actions are resolved, so host-registered actions (including ones
     * that open modals) stay resolvable on subsequent requests.
     */
    public function cacheHasCommentActions(): void
    {
        foreach ($this->getCustomActions() as $action) {
            $this->cacheAction($action);
        }
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->label(__('commentions::comments.edit'))
            ->icon('heroicon-s-pencil-square')
            ->iconButton()
            ->size('xs')
            ->color('gray')
            ->visible(fn (): bool => $this->commentUserCan('update'))
            ->action(fn () => $this->edit());
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label(__('commentions::comments.delete'))
            ->icon('heroicon-s-trash')
            ->iconButton()
            ->size('xs')
            ->color('gray')
            ->visible(fn (): bool => $this->commentUserCan('delete'))
            ->requiresConfirmation()
            ->modalHeading(__('commentions::comments.delete_comment_heading'))
            ->modalDescription(__('commentions::comments.delete_comment_body'))
            ->modalSubmitActionLabel(__('commentions::comments.delete'))
            ->action(fn () => $this->delete());
    }

    /**
     * Custom actions registered by the host application via
     * Config::registerCommentActions(), rendered after edit/delete.
     *
     * @return array<Action>
     */
    public function getCustomActions(): array
    {
        if (! $this->comment instanceof CommentModel) {
            return [];
        }

        return $this->customActions ??= Config::getCommentActions($this->comment);
    }

    protected function commentUserCan(string $ability): bool
    {
        return $this->comment instanceof CommentModel
            && (bool) Config::resolveAuthenticatedUser()?->can($ability, $this->comment);
    }
}
