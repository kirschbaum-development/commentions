<?php

namespace Kirschbaum\Commentions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\CommentReaction;

class CommentWasReactedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Comment $comment,
        public CommentReaction $reaction,
    ) {}
}
