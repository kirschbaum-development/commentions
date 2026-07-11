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

        return 'comm:prose comm:dark:prose-invert comm:prose-sm comm:sm:prose-base comm:lg:prose-lg comm:xl:prose-2xl comm:focus:outline-none comm:p-4 comm:min-w-full comm:w-full';
    }

    /**
     * Resolve the configured editor toolbar buttons, normalized into groups.
     *
     * @return array<int, array<int, string>>
     */
    public static function getToolbarButtons(): array
    {
        if (! config('commentions.toolbar.enabled', true)) {
            return [];
        }

        return static::normalizeToolbarButtons(config('commentions.toolbar.buttons', []));
    }

    /**
     * Normalize a flat or grouped list of toolbar buttons into groups, so the
     * editor can always render groups separated by visual dividers. Non-string
     * buttons and empty groups are dropped, so malformed configuration can
     * never reach the editor view.
     *
     * @param  array<mixed>  $buttons
     * @return array<int, array<int, string>>
     */
    public static function normalizeToolbarButtons(array $buttons): array
    {
        if ($buttons === []) {
            return [];
        }

        $isGrouped = array_is_list($buttons)
            && count(array_filter($buttons, 'is_array')) === count($buttons);

        $groups = $isGrouped ? $buttons : [$buttons];

        $normalized = [];

        foreach ($groups as $group) {
            $group = array_values(array_filter(
                is_array($group) ? $group : [$group],
                'is_string',
            ));

            if ($group !== []) {
                $normalized[] = $group;
            }
        }

        return $normalized;
    }

    public static function getComponentPrefix(): string
    {
        return static::isLivewireV4() ? 'commentions.' : 'commentions::';
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
