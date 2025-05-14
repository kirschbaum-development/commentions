<?php

namespace Kirschbaum\Commentions;

use Closure;
use InvalidArgumentException;
use Kirschbaum\Commentions\Contracts\Commenter;

class Config
{
    protected static ?string $guard = null;

    protected static ?Closure $resolveAuthenticatedUser = null;

    public static function resolveAuthenticatedUserUsing(Closure $callback): void
    {
        static::$resolveAuthenticatedUser = $callback;
    }

    public static function resolveAuthenticatedUser(): ?Commenter
    {
        $resolver = static::$resolveAuthenticatedUser;
        $user = $resolver ? call_user_func($resolver) : auth()->guard(static::$guard)->user();

        if ($user !== null && ! ($user instanceof Commenter)) {
            throw new InvalidArgumentException('Resolved user must implement ' . Commenter::class);
        }

        return $user;
    }

    public static function getCommentModel(): string
    {
        return config('commentions.comment.model', Comment::class);
    }

    public static function getCommenterModel(): string
    {
        return config('commentions.commenter.model');
    }

    public static function getAllowedReactions(): array
    {
        return config('commentions.reactions.allowed', ['👍']);
    }
}
