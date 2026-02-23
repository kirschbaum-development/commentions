<?php

namespace Kirschbaum\Commentions;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;
use Kirschbaum\Commentions\Listeners\SendUserMentionedNotification;
use Kirschbaum\Commentions\Livewire\Comment;
use Kirschbaum\Commentions\Livewire\CommentList;
use Kirschbaum\Commentions\Livewire\Comments;
use Kirschbaum\Commentions\Livewire\Reactions;
use Kirschbaum\Commentions\Livewire\SubscriptionSidebar;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CommentionsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'commentions';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasMigrations([
                'create_commentions_tables',
                'create_commentions_reactions_table',
                'create_commentions_subscriptions_table',
            ]);
    }

    public function packageBooted(): void
    {
        $prefix = Config::getComponentPrefix();

        Livewire::component($prefix . 'comment', Comment::class);
        Livewire::component($prefix . 'comment-list', CommentList::class);
        Livewire::component($prefix . 'comments', Comments::class);
        Livewire::component($prefix . 'reactions', Reactions::class);
        Livewire::component($prefix . 'subscription-sidebar', SubscriptionSidebar::class);

        // Share component prefix with views for dynamic component names
        View::share('commentionsComponentPrefix', $prefix);

        FilamentAsset::register(
            [
                Js::make('commentions-scripts', __DIR__ . '/../resources/dist/commentions.js')->module(),
            ],
            'kirschbaum-development/' . static::$name
        );

        FilamentAsset::register(
            [
                Css::make('commentions', __DIR__ . '/../resources/dist/commentions.css'),
            ],
            'kirschbaum-development/' . static::$name
        );

        Gate::policy(CommentModel::class, config('commentions.comment.policy'));

        // Allow publishing of translation files with a custom tag
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/commentions'),
        ], 'commentions-lang');

        if (config('commentions.notifications.mentions.enabled', false)) {
            $listenerClass = (string) config('commentions.notifications.mentions.listener', SendUserMentionedNotification::class);
            Event::listen(UserWasMentionedEvent::class, $listenerClass);
        }
    }
}
