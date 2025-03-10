![](screenshots/commentions-logo.png)

![Laravel Supported Versions](https://img.shields.io/badge/laravel-10.x/11.x/12.x-green.svg)
[![tests](https://github.com/kirschbaum-development/commentions/actions/workflows/tests.yml/badge.svg)](https://github.com/kirschbaum-development/commentions/actions/workflows/tests.yml)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/kirschbaum-development/commentions.svg?style=flat-square)](https://packagist.org/packages/kirschbaum-development/commentions)
[![Total Downloads](https://img.shields.io/packagist/dt/kirschbaum-development/commentions.svg?style=flat-square)](https://packagist.org/packages/kirschbaum-development/commentions)

Commentions is a drop-in package for Filament that allows you to add comments to your resources. You can configure it so your users are mentionable in the comments, and it dispatches events so you can handle mentions in your own application however you like.

![](screenshots/comments.png)

## Installation

```bash
composer require kirschbaum-development/commentions
```

## Usage

1. Publish the migrations

```bash
php artisan vendor:publish --tag="commentions-migrations"
```

2. In your `User` model implement the `Commenter` interface.

```php
use Kirschbaum\Commentions\Contracts\Commenter;

class User extends Model implements Commenter
{
    // ...
}
```

3. In the model you want to add comments, implement the `Commentable` interface and the `HasComments` trait.

```php
use Kirschbaum\Commentions\HasComments;
use Kirschbaum\Commentions\Contracts\Commentable;

class Project extends Model implements Commentable
{
    use HasComments;
}
```

### Usage with Filament

You can register the plugin in your Panel(s) and configure editing and deleting permissions:

```php
use Kirschbaum\Commentions\CommentionsPlugin;

return $panel
    ->plugins([
        CommentionsPlugin::make()
            ->disallowEdits()    // Prevent users from editing their comments
            ->disallowDeletes()  // Prevent users from deleting their comments
    ])
```

There are a couple of ways to use Commentions with Filament.

1. Register the component in your Filament Infolists:

```php
Infolists\Components\Section::make('Comments')
    ->schema([
        CommentsEntry::make('comments')
            ->mentionables(fn (Model $record) => User::all()),
    ]),
```

2. Or in your table actions:

```php
use Kirschbaum\Commentions\Filament\Actions\CommentsTableAction;

->actions([
    CommentsTableAction::make()
        ->mentionables(User::all())
])
```

3. Or as a header action:

```php
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

protected function getHeaderActions(): array
{
    return [
        CommentsAction::make(),
    ];
}
```

***

### Configuring the User model and the mentionables

If your `User` model lives in a different namespace than `App\Models\User`, you can configure it in `config/commentions.php`:

```php
    'commenter' => [
        'model' => \App\Domains\Users\User::class,
    ],
```

### Disabling comment editing and deletion

By default, users can edit and delete their own comments. You can disable this functionality in two ways:

#### 1. Using the plugin configuration

```php
use Kirschbaum\Commentions\CommentionsPlugin;

return $panel
    ->plugins([
        CommentionsPlugin::make()
            ->disallowEdits()    // Prevent users from editing their comments
            ->disallowDeletes()  // Prevent users from deleting their comments
    ])
```

#### 2. Using the configuration file

Set the `allow_edits` and `allow_deletes` options in your `config/commentions.php` file:

```php
    /**
     * Comment editing/deleting options.
     */
    'allow_edits' => false,
    'allow_deletes' => false,
```

> **Note:** The plugin configuration takes precedence over the config file settings.

By default, the `name` property will be used to render the mention names. You can customize it either by implementing the Filament `HasName` interface OR by implementing the optional `getCommenterName` method.

```php
use Filament\Models\Contracts\HasName;
use Kirschbaum\Commentions\Contracts\Commenter;

class User extends Model implements Commenter, HasName
{
    public function getFilamentName(): string
    {
        return (string) '#' . $this->id . ' - ' . $this->name;
    }
}
```

```php
use Kirschbaum\Commentions\Contracts\Commenter;

class User extends Model implements Commenter
{
    public function getCommenterName(): string
    {
        return (string) '#' . $this->id . ' - ' . $this->name;
    }
}
```

### Sending notifications when a user is mentioned

Every time a user is mentioned, the `Kirschbaum\Commentions\Events\UserWasMentionedEvent` is dispatched. You can listen to this event and send notifications to the mentioned user.

Example usage:

```php
namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\UserMentionedInCommentNotification;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;

class SendUserMentionedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserWasMentionedEvent $event): void
    {
        $event->user->notify(
            new UserMentionedInCommentNotification($event->comment)
        );
    }
}
```

If you have [event auto-discovery](https://laravel.com/docs/11.x/events#registering-events-and-listeners), this should be enough. Otherwise, make sure to register your listener on the `EventServiceProvider`.

### Resolving the authenticated user

By default, when a new comment is made, the `Commenter` is automatically set to the current user logged in user (`auth()->user()`). If you want to change this behavior, you can implement your own resolver:

```php
use Kirschbaum\Commentions\Config;

Config::resolveAuthenticatedUserUsing(
    fn () => auth()->guard('my-guard')->user()
)
```

### Getting the mentioned Commenters from an existing comment

```php
$comment->getMentioned()->each(function (Commenter $commenter) {
    // do something with $commenter...
});
```

### Polling for new comments

Commentions supports polling for new comments. You can enable it on any component by calling the `pollingInterval` method and passing the number of seconds.

```php
Infolists\Components\Section::make('Comments')
    ->schema([
        CommentsEntry::make('comments')
            ->pollingInterval(10)
    ]),
```

### Rendering non-Comments in the list

Sometimes you might want to render non-Comments in the list of comments. For example, you might want to render when the status of a project is changed. For this, you can override the `getComments` method in your model, and return instances of the `Kirschbaum\Commentions\RenderableComment` data object.

```php
use Kirschbaum\Commentions\RenderableComment;

public function getComments(): Collection
{
    $statusHistory = $this->statusHistory()->get()->map(fn (StatusHistory $statusHistory) => new RenderableComment(
        id: $statusHistory->id,
        authorName: $statusHistory->user->name,
        body: sprintf('Status changed from %s to %s', $statusHistory->old_status, $statusHistory->new_status),
        createdAt: $statusHistory->created_at,
    ));

    $comments = $this->comments()->latest()->with('author')->get();

    return $statusHistory->merge($comments);
}
```

***

## Security

If you discover any security related issues, please email security@kirschbaumdevelopment.com instead of using the issue tracker.

## Credits

- [Luis Dalmolin](https://github.com/luisdalmolin)

## Sponsorship

Development of this package is sponsored by Kirschbaum Development Group, a developer driven company focused on problem solving, team building, and community. Learn more [about us](https://kirschbaumdevelopment.com?utm_source=github) or [join us](https://careers.kirschbaumdevelopment.com?utm_source=github)!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
