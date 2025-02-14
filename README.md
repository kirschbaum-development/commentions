# Filament Comments

Filament Comments is a drop-in package for Filament that allows you to add comments to your resources. It includes mentions, and it dispatches events so you can handle mentions in your own application however you like.

## Installation

```bash
composer require kirschbaum-development/filament-comments
```

## Usage

1. Publish the migrations

```bash
php artisan vendor:publish --tag="filament-comments-migrations"
```

2. In your `User` model implement the `CommentAuthor` interface.

```php
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;

class User extends Model implements CommentAuthor
{
    // ...
}
```

3. In the model you want to add comments, implement the `Commentable` interface and the `HasComments` trait.

```php
use Kirschbaum\FilamentComments\HasComments;
use Kirschbaum\FilamentComments\Contracts\Commentable;

class Project extends Model implements Commentable
{
    use HasComments;
}
```

4. Add the following paths to your `tailwind.config.js` content:

```js
export default {
    // ...
    content: [
        // ...
        './vendor/kirschbaum-development/filament-comments/resources/views/**/*.blade.php',
        './vendor/kirschbaum-development/filament-comments/src/**/*.php',
    ],
}
```

5. And register the Livewire component in your Filament resources:

```php
Infolists\Components\Section::make('Comments')
    ->schema([
        Livewire::make(Comments::class, [
            'mentionables' => User::all()->toArray(),
        ])
    ]),
```

And that's it!

![](screenshots/comments.png)

## TODO

- [ ] Dark mode support
- [ ] More examples on how to integrate in other Filament parts
