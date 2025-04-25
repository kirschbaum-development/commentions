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
use Livewire\Component;

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

    public function render()
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
    public function toggleReaction(string $reactionType)
    {
        if (! $this->comment instanceof CommentModel) {
            Log::warning('Attempted to react to a non-CommentModel instance.', ['comment_id' => $this->comment->getId()]);
            return;
        }

        $user = Config::resolveAuthenticatedUser();

        if (! $user) {
            return;
        }

        $existingReaction = $this->comment->reactions()
            ->where('reactor_id', $user->getKey())
            ->where('reactor_type', $user->getMorphClass())
            ->where('reaction', $reactionType)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
        } else {
            $this->comment->reactions()->create([
                'reactor_id' => $user->getKey(),
                'reactor_type' => $user->getMorphClass(),
                'reaction' => $reactionType,
            ]);
        }

        $this->dispatch('comment:reactions-updated');
    }

    #[Computed]
    public function reactionSummary()
    {
        if (! $this->comment instanceof CommentModel) {
            return [];
        }

        if (! $this->comment->relationLoaded('reactions')) {
            $this->comment->load('reactions.reactor');
        }

        return $this->comment->reactions
            ->groupBy('reaction')
            ->map(function ($group) {
                $user = Config::resolveAuthenticatedUser();

                return [
                    'count' => $group->count(),
                    'reacted_by_current_user' => $user && $group->contains(fn ($reaction) => $reaction->reactor_id == $user->getKey() && $reaction->reactor_type == $user->getMorphClass()),
                ];
            })
            ->sortByDesc('count')
            ->toArray();
    }
}
