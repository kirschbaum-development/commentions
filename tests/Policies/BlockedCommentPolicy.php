<?php

namespace Tests\Policies;

use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Policies\CommentPolicy;

class BlockedCommentPolicy extends CommentPolicy
{
    public function create(Commenter $user): bool
    {
        return false;
    }

    public function update($user, Comment $comment): bool
    {
        return false;
    }

    public function delete($user, Comment $comment): bool
    {
        return false;
    }
}
