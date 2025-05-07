<?php

namespace Kirschbaum\Commentions;

use Closure;
use Kirschbaum\Commentions\Contracts\Commenter;

class Config
{
    protected static ?string $guard = null;

    protected static ?Closure $resolveAuthenticatedUser = null;

    public static function resolveAuthenticatedUserUsing(Closure $callback): void
    {
        static::$resolveAuthenticatedUser = $callback;
    }

    public static function resolveAuthenticatedUser(): Commenter
    {
        return static::$resolveAuthenticatedUser
            ? call_user_func(static::$resolveAuthenticatedUser)
            : auth()->guard(static::$guard)->user();
    }

    public static function getCommenterModel(): string
    {
        return config('commentions.commenter.model');
    }
}
