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

2. In your `User` model implement the `Commenter` interface.

```php
use Kirschbaum\FilamentComments\Contracts\Commenter;

class User extends Model implements Commenter
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

4. And register the Livewire component in your Filament resources:

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

### Configuring the User model and the mentionables

By default, the `Commenter`

### Sending notifications when a user is mentioned

Every time a user is mentioned, the `Kirschbaum\FilamentComments\Events\UserWasMentionedEvent` is dispatched. You can listen to this event and send notifications to the mentioned user.

Example usage: 

```php
namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\UserMentionedInCommentNotification;
use Kirschbaum\FilamentComments\Events\UserWasMentionedEvent;

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

### Customizing the Commenter

By default, when a new comment is made, the `Commenter` is automatically set to the current user logged in user (`auth()->user()`). If you want to change this behavior, you can implement your own resolver:

```php
use Kirschbaum\FilamentComments\FilamentComments;

FilamentComments::resolveAuthenticatedUserUsing(
    fn () => auth()->guard('my-guard')->user()
)
```

## Security

If you discover any security related issues, please email security@kirschbaumdevelopment.com instead of using the issue tracker.

## Credits

- [Luis Dalmolin](https://github.com/luisdalmolin)

## Sponsorship

Development of this package is sponsored by Kirschbaum Development Group, a developer driven company focused on problem solving, team building, and community. Learn more [about us](https://kirschbaumdevelopment.com?utm_source=github) or [join us](https://careers.kirschbaumdevelopment.com?utm_source=github)!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
