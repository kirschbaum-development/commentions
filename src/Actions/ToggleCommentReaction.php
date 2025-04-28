<?php

namespace Kirschbaum\Commentions\Actions;

use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\CommentReaction;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Events\CommentReactionToggledEvent;

class ToggleCommentReaction
{
    public static function run(Comment $comment, string $reaction, ?Commenter $user = null): void
    {
        if (! $user) {
            return;
        }

        if (! in_array($reaction, Config::getAllowedReactions())) {
            return;
        }

        /** @var CommentReaction $existingReaction */
        $existingReaction = $comment
            ->reactions()
            ->where('reactor_id', $user->getKey())
            ->where('reactor_type', $user->getMorphClass())
            ->where('reaction', $reaction)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
            event(new CommentReactionToggledEvent($comment, $existingReaction, $user, $reaction, false));
        } else {
            $newReaction = $comment->reactions()->create([
                'reactor_id' => $user->getKey(),
                'reactor_type' => $user->getMorphClass(),
                'reaction' => $reaction,
            ]);
            event(new CommentReactionToggledEvent($comment, $newReaction, $user, $reaction, true));
        }
    }
}
