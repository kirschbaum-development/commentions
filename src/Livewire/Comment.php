<?php

namespace Kirschbaum\Commentions\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Contracts\RenderableComment;
use Kirschbaum\Commentions\Livewire\Concerns\HasCommentActions;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasRatings;
use Kirschbaum\Commentions\Livewire\Concerns\HasToolbarButtons;
use Kirschbaum\Commentions\Livewire\Concerns\InteractsWithCommentSchemas;
use Kirschbaum\Commentions\Livewire\Concerns\InteractsWithCommentSchemasBridge;
use Kirschbaum\Commentions\Livewire\Concerns\IsReadonly;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Comment extends Component implements HasActions, HasForms
{
    use HasCommentActions;
    use HasMentions;
    use HasRatings;
    use HasToolbarButtons;
    use InteractsWithActions;
    use InteractsWithCommentSchemas;
    use InteractsWithCommentSchemasBridge;
    use IsReadonly;

    public CommentModel|RenderableComment $comment;

    public string $commentBody = '';

    public bool $editing = false;

    public ?int $rating = null;

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
    public function delete(): void
    {
        if ($this->isReadonly() || ! auth()->user()?->can('delete', $this->comment)) {
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
        if ($this->isReadonly() || ! Config::resolveAuthenticatedUser()?->can('update', $this->comment)) {
            return;
        }

        $this->editing = true;
        $this->commentBody = $this->comment->body;
        $this->rating = $this->comment->rating;

        $this->dispatch('comment:updated');
    }

    public function updateComment(): void
    {
        if ($this->isReadonly() || ! Config::resolveAuthenticatedUser()?->can('update', $this->comment)) {
            return;
        }

        $attributes = ['body' => $this->commentBody];

        if ($this->ratingsAreEnabled()) {
            $this->validate([
                'rating' => ['nullable', 'integer', 'min:1', 'max:' . $this->getMaxRating()],
            ]);

            $attributes['rating'] = $this->rating;
        }

        $this->comment->update($attributes);

        $this->editing = false;
    }

    public function cancelEditing(): void
    {
        $this->editing = false;
        $this->commentBody = '';
        $this->rating = null;
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
}
