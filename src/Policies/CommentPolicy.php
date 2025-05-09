<?php

namespace Kirschbaum\Commentions\Policies;

use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commenter;

class CommentPolicy
{
    public function create(Commenter $user): bool
    {
        return true;
    }

    public function update($user, Comment $comment): bool
    {
        return $comment->isAuthor($user);
    }

    public function delete($user, Comment $comment): bool
    {
        return $comment->isAuthor($user);
    }
}
