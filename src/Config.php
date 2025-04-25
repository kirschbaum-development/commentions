<?php

namespace Kirschbaum\Commentions;

use Closure;
use Kirschbaum\Commentions\Contracts\Commenter;

class Config
{
    protected static ?string $guard = null;

    protected static ?Closure $resolveAuthenticatedUser = null;

    protected static ?bool $allowEdits = null;

    protected static ?bool $allowDeletes = null;

    public static function resolveAuthenticatedUserUsing(Closure $callback): void
    {
        static::$resolveAuthenticatedUser = $callback;
    }

    public static function resolveAuthenticatedUser(): ?Commenter
    {
        $resolver = static::$resolveAuthenticatedUser;
        $user = $resolver ? call_user_func($resolver) : auth()->guard(static::$guard)->user();

        if ($user !== null && ! ($user instanceof Commenter)) {
            throw new \InvalidArgumentException('Resolved user must implement ' . Commenter::class);
        }

        return $user;
    }

    public static function getCommenterModel(): string
    {
        return config('commentions.commenter.model');
    }

    public static function allowEdits(?bool $allow = null): ?bool
    {
        if (is_bool($allow)) {
            static::$allowEdits = $allow;

            return null;
        }

        return static::$allowEdits ?? config('commentions.allow_edits', true);
    }

    public static function allowDeletes(?bool $allow = null): ?bool
    {
        if (is_bool($allow)) {
            static::$allowDeletes = $allow;

            return null;
        }

        return static::$allowDeletes ?? config('commentions.allow_deletes', true);
    }

    public static function getAllowedReactions(): array
    {
        return config('commentions.allowed_reactions', ['👍']);
    }
}
