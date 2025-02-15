<?php

namespace Kirschbaum\FilamentComments\Events;

use Illuminate\Queue\SerializesModels;
use Kirschbaum\FilamentComments\Comment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Kirschbaum\FilamentComments\Contracts\Commenter;

class UserWasMentionedEvent
{
    use Dispatchable;
    use SerializesModels;
    use InteractsWithSockets;

    public readonly Comment $comment;

    public readonly Commenter $user;

    public function __construct($comment, $user)
    {
        $this->comment = $comment;
        $this->user = $user;
    }
}
