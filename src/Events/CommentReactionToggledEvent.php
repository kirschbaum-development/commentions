<?php

namespace Kirschbaum\Commentions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\CommentReaction;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CommentReactionToggledEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Comment $comment,
        public ?CommentReaction $reaction,
        public Authenticatable $user,
        public string $reactionType,
        public bool $wasCreated
    ) {
    }
}
