<?php

namespace Kirschbaum\Commentions;

use Closure;
use Composer\InstalledVersions;
use InvalidArgumentException;
use Kirschbaum\Commentions\Contracts\Commenter;

class Config
{
    protected static ?string $guard = null;

    protected static ?Closure $resolveAuthenticatedUser = null;

    protected static ?Closure $resolveCommentUrl = null;

    protected static ?Closure $resolveTipTapCssClasses = null;

    protected static ?Closure $resolveTimezone = null;

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

    public static function getCommentTable(): string
    {
        return config('commentions.tables.comments', 'comments');
    }

    public static function getCommentReactionTable(): string
    {
        return config('commentions.tables.comment_reactions', 'comment_reactions');
    }

    public static function getCommentSubscriptionTable(): string
    {
        return config('commentions.tables.comment_subscriptions', 'comment_subscriptions');
    }

    public static function resolveCommentUrlUsing(Closure $callback): void
    {
        static::$resolveCommentUrl = $callback;
    }

    public static function resolveCommentUrl(?Comment $comment): ?string
    {
        if ($comment === null) {
            return null;
        }

        if (static::$resolveCommentUrl instanceof Closure) {
            return call_user_func(static::$resolveCommentUrl, $comment);
        }

        return null;
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

    public static function resolveTipTapCssClassesUsing(Closure $callback): void
    {
        static::$resolveTipTapCssClasses = $callback;
    }

    public static function getTipTapCssClasses(): ?string
    {
        if (static::$resolveTipTapCssClasses instanceof Closure) {
            return call_user_func(static::$resolveTipTapCssClasses);
        }

        return 'comm:prose comm:dark:prose-invert comm:prose-sm comm:sm:prose-base comm:lg:prose-lg comm:xl:prose-2xl comm:focus:outline-none comm:p-4 comm:min-w-full comm:w-full comm:rounded-lg comm:border comm:border-gray-300 comm:dark:border-gray-700';
    }

    public static function getComponentPrefix(): string
    {
        return static::isLivewireV4() ? 'commentions.' : 'commentions::';
    }

    public static function resolveTimezoneUsing(Closure $callback): void
    {
        static::$resolveTimezone = $callback;
    }

    public static function getTimezone(): ?string
    {
        if (static::$resolveTimezone instanceof Closure) {
            return call_user_func(static::$resolveTimezone);
        }

        return config('commentions.timezone');
    }

    public static function applyTimezone(\DateTime|\Carbon\CarbonInterface $dt): \DateTime|\Carbon\CarbonInterface
    {
        $tz = static::getTimezone();

        if ($tz === null) {
            return $dt;
        }

        if ($dt instanceof \Carbon\CarbonInterface) {
            return $dt->copy()->setTimezone($tz);
        }

        $cloned = clone $dt;
        $cloned->setTimezone(new \DateTimeZone($tz));

        return $cloned;
    }

    public static function isLivewireV4(): bool
    {
        return version_compare(
            InstalledVersions::getVersion('livewire/livewire') ?? '0.0',
            '4.0',
            '>='
        );
    }
}
