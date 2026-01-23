<?php

namespace Kirschbaum\Commentions;

use Composer\InstalledVersions;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
        if ($this->isLivewireV4()) {
            Livewire::addNamespace('commentions', classNamespace: __NAMESPACE__ . '\\Livewire');
        } else {
            Livewire::component('commentions::comment', Comment::class);
            Livewire::component('commentions::comment-list', CommentList::class);
            Livewire::component('commentions::comments', Comments::class);
            Livewire::component('commentions::reactions', Reactions::class);
            Livewire::component('commentions::subscription-sidebar', SubscriptionSidebar::class);
        }

        FilamentAsset::register(
            [
                Js::make('commentions-scripts', __DIR__ . '/../resources/dist/commentions.js'),
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

    protected function isLivewireV4(): bool
    {
        return version_compare(
            InstalledVersions::getVersion('livewire/livewire') ?? '0.0',
            '4.0',
            '>='
        );
    }
}
