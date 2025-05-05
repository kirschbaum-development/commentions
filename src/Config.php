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
}
