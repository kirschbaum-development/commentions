<?php

namespace Kirschbaum\Commentions\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Contracts\RenderableComment;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class Comment extends Component
{
    use HasMentions;

    public CommentModel|RenderableComment $comment;

    public string $commentBody = '';

    public bool $editing = false;

    public bool $showDeleteModal = false;

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
        if (! $this->comment->canDelete()) {
            return;
        }

        $this->comment->delete();
        $this->showDeleteModal = false;

        $this->dispatch('comment:deleted');

        Notification::make()
            ->title('Comment deleted')
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
        if (! $this->comment->canEdit()) {
            return;
        }

        $this->editing = true;
        $this->commentBody = $this->comment->body;

        $this->dispatch('comment:updated');
    }

    public function updateComment()
    {
        if (! $this->comment->canEdit()) {
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

    #[Renderless]
    public function toggleReaction(string $reaction): void
    {
        if (! $this->comment instanceof CommentModel) {
            return;
        }

        $this->comment->toggleReaction($reaction);

        $this->dispatch('comment:reaction:saved');
    }
}
