<?php

namespace Kirschbaum\Commentions;

use Livewire\Livewire;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Spatie\LaravelPackageTools\Package;
use Filament\Support\Facades\FilamentAsset;
use Kirschbaum\Commentions\Livewire\Comment;
use Kirschbaum\Commentions\Livewire\Reactions;
use Kirschbaum\Commentions\Livewire\Comments;
use Kirschbaum\Commentions\Livewire\CommentList;
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
            ->hasViews()
            ->hasMigrations([
                'create_commentions_tables',
                'create_commentions_reactions_table',
            ]);
    }

    public function packageBooted(): void
    {
        Livewire::component('commentions::comment', Comment::class);
        Livewire::component('commentions::comment-list', CommentList::class);
        Livewire::component('commentions::comments', Comments::class);
        Livewire::component('commentions::reactions', Reactions::class);

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
    }
}
