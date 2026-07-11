<?php

namespace Kirschbaum\Commentions\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Contracts\RenderableComment;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Comment extends Component
{
    use HasMentions;

    /**
     * Deepest level that still receives added horizontal indent. Beyond this
     * the thread line still renders, but no extra padding is added so deep
     * threads don't compound horizontally on small screens.
     */
    public const INDENT_CAP_DEPTH = 2;

    public CommentModel|RenderableComment $comment;

    public string $commentBody = '';

    public bool $editing = false;

    public bool $replying = false;

    public int $depth = 0;

    public ?string $tipTapCssClasses = null;

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    #[On('comment:reaction:toggled')]
    public function handleReactionToggledEvent(string $reaction, int $commentId): void
    {
        if ($this->comment->getId() !== $commentId) {
            return;
        }

        $this->toggleReaction($reaction);
    }

    #[Renderless]
    public function delete()
    {
        if (! auth()->user()?->can('delete', $this->comment)) {
            return;
        }

        $this->comment->delete();

        $this->dispatch('comment:deleted');

        Notification::make()
            ->title(__('commentions::comments.notification_comment_deleted'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('commentions::comment');
    }

    #[On('body:updated')]
    #[Renderless]
    public function updateCommentBodyContent($value): void
    {
        $this->commentBody = $value;
    }

    #[Renderless]
    public function clear(): void
    {
        $this->commentBody = '';

        $this->dispatch('comment:content:cleared');
    }

    public function edit(): void
    {
        if (! Config::resolveAuthenticatedUser()?->can('update', $this->comment)) {
            return;
        }

        $this->editing = true;
        $this->commentBody = $this->comment->body;

        $this->dispatch('comment:updated');
    }

    public function updateComment()
    {
        if (! Config::resolveAuthenticatedUser()?->can('update', $this->comment)) {
            return;
        }

        $this->comment->update([
            'body' => $this->commentBody,
        ]);

        $this->editing = false;
    }

    public function cancelEditing()
    {
        $this->editing = false;
        $this->commentBody = '';
    }

    public function reply(): void
    {
        if (! $this->comment instanceof CommentModel) {
            return;
        }

        $this->editing = false;
        $this->replying = true;
        $this->commentBody = '';
    }

    public function saveReply(): void
    {
        if (! $this->comment instanceof CommentModel) {
            return;
        }

        $user = Config::resolveAuthenticatedUser();

        if (! $user) {
            return;
        }

        if ($this->comment->depth() >= $this->maxReplyDepth()) {
            return;
        }

        $this->validate();

        SaveComment::run(
            $this->comment->commentable,
            $user,
            $this->commentBody,
            (int) $this->comment->getId(),
        );

        $this->replying = false;
        $this->commentBody = '';

        $this->dispatch('comment:saved');
        $this->dispatch('comment:content:cleared');
    }

    public function cancelReplying(): void
    {
        $this->replying = false;
        $this->commentBody = '';
    }

    #[Renderless]
    public function toggleReaction(string $reaction): void
    {
        if (! $this->comment instanceof CommentModel) {
            return;
        }

        $this->comment->toggleReaction($reaction);

        $this->dispatch('comment:reaction:saved');
    }

    public function getTipTapCssClasses(): ?string
    {
        return $this->tipTapCssClasses ?? Config::getTipTapCssClasses();
    }

    /**
     * Whether the current user may post a reply to this comment, given that
     * threading is enabled and the comment is not already at the max depth.
     */
    public function canReply(): bool
    {
        return $this->comment instanceof CommentModel
            && (bool) config('commentions.threading.enabled', false)
            && $this->depth < $this->maxReplyDepth()
            && (bool) Config::resolveAuthenticatedUser()?->can('create', Config::getCommentModel());
    }

    /**
     * Whether the replies wrapper rendered by this comment should add
     * horizontal indent. Past the cap the thread line still renders.
     */
    public function shouldIndentReplies(): bool
    {
        return $this->depth < self::INDENT_CAP_DEPTH;
    }

    protected function maxReplyDepth(): int
    {
        return max(0, (int) config('commentions.threading.max_depth', 3));
    }
}
