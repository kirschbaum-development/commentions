<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Contracts\View\View;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Contracts\RenderableComment;
use Kirschbaum\Commentions\Livewire\Concerns\IsReadonly;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Reactions extends Component
{
    use IsReadonly;

    public RenderableComment $comment;

    public function handleReactionToggle(string $reaction): void
    {
        if ($this->isReadonly()) {
            return;
        }

        $this->dispatch(
            'comment:reaction:toggled',
            reaction: $reaction,
            commentId: $this->comment->getId()
        )->to(Comment::class);

        unset($this->reactionSummary);
    }

    public function render(): View
    {
        return view('commentions::reactions', [
            'allowedReactions' => Config::getAllowedReactions(),
        ]);
    }

    #[On('comment:reaction:saved')]
    public function refreshReactionSummary()
    {
        unset($this->reactionSummary);
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
                    'reaction' => $group->first()->reaction,
                    'reacted_by_current_user' => $user && $group->contains(fn ($reaction) => $reaction->reactor_id == $user->getKey() && $reaction->reactor_type == $user->getMorphClass()),
                ];
            })
            ->sortByDesc('count')
            ->toArray();
    }
}
