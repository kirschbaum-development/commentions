![](screenshots/commentions-logo.png)

![Laravel Supported Versions](https://img.shields.io/badge/laravel-10.x/11.x/12.x-green.svg)
![Filament Supported Versions](https://img.shields.io/badge/filament-3.x/4.x-green.svg)
[![CI](https://github.com/kirschbaum-development/commentions/actions/workflows/ci.yml/badge.svg)](https://github.com/kirschbaum-development/commentions/actions/workflows/ci.yml)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/kirschbaum-development/commentions.svg?style=flat-square)](https://packagist.org/packages/kirschbaum-development/commentions)
[![Total Downloads](https://img.shields.io/packagist/dt/kirschbaum-development/commentions.svg?style=flat-square)](https://packagist.org/packages/kirschbaum-development/commentions)

Commentions is a drop-in package for Filament that allows you to add comments to your resources. You can configure it so your users are mentionable in the comments, and it dispatches events so you can handle mentions in your own application however you like.

![](screenshots/comments-demo.png)

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

There are a couple of ways to use Commentions with Filament.

1. Register the component in your Filament Infolists:

> This works for Filament 3 and 4.

```php
    CommentsEntry::make('comments')
        ->mentionables(fn (Model $record) => User::all()),
```

If you wish to make the comments more distinct from the rest of the page, we recommend wrapping them in a `Section`.

For Filament 3:

```php
\Filament\Infolists\Components\Section::make('Comments')
    ->schema([
        CommentsEntry::make('comments'),
    ]),
```

For Filament 4:

```php
\Filament\Schemas\Components\Section::make('Comments')
    ->components([
        CommentsEntry::make('comments'),
    ]),
```

2. Or in your table actions:

If you are using Filament 3, you must use `CommentsTableAction` in your table's `actions` array:

```php
use Kirschbaum\Commentions\Filament\Actions\CommentsTableAction;

->actions([
    CommentsTableAction::make()
        ->mentionables(User::all())
])
```

If you are using Filament 4, you should use `CommentsAction` in `recordActions` instead:

```php
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

->recordActions([
    CommentsAction::make()
        ->mentionables(User::all())
])
```

3. Or as a header action:

> This works for Filament 3 and 4.

```php
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

protected function getHeaderActions(): array
{
    return [
        CommentsAction::make(),
    ];
}
```

4. Or directly in form schemas (Filament 4):

> This works for Filament 4 with the new Schema system.

```php
use Filament\Forms\Components\ViewField;

public static function configure(Schema $schema): Schema
{
    return $schema
        ->components([
            // Your other form fields...
            
            ViewField::make('comments_section')
                ->view('commentions::filament.forms.comments-section')
                ->viewData(fn ($livewire) => ['record' => $livewire->record ?? null])
                ->columnSpanFull()
                ->hiddenLabel(),
        ]);
}
```

### Subscription Management

Commentions includes a subscription system that allows users to subscribe to receive notifications when new comments are added to a commentable resource.

#### Subscription Actions

You can add subscription actions to your Filament resources:

```php
use Kirschbaum\Commentions\Filament\Actions\SubscriptionAction;

// In header actions
protected function getHeaderActions(): array
{
    return [
        SubscriptionAction::make(),
    ];
}

// In table actions (Filament 3)
->actions([
    SubscriptionTableAction::make(),
])

// In record actions (Filament 4)
->recordActions([
    SubscriptionAction::make(),
])
```

#### Subscription Sidebar

When using comments in modals, a subscription sidebar is automatically displayed showing:
- Subscribe/unsubscribe button for the current user
- List of users currently subscribed to the commentable
- Real-time updates when subscription status changes

##### Livewire options

When using the `commentions::comments` Livewire component directly, you can control the sidebar and its contents via component properties:

- `sidebarEnabled` (bool, default: true): toggles the entire subscription sidebar
- `showSubscribers` (bool, default: `config('commentions.subscriptions.show_subscribers', true)`): toggles the subscribers list within the sidebar

Examples:

```php
// Hide the sidebar entirely
<livewire:commentions::comments :record="$record" :sidebar-enabled="false" />

// Keep the sidebar, but hide the subscribers list (uses config default if omitted)
<livewire:commentions::comments :record="$record" :show-subscribers="false" />
```

Inside the component/template you can also rely on these computed properties:

- `canSubscribe`: whether the current user can subscribe
- `isSubscribed`: whether the current user is subscribed to the current record
- `subscribers`: a collection of current subscribers

The component exposes a `toggleSubscription()` action which subscribes/unsubscribes the current user.

#### Disabling the Subscription Sidebar

You can disable the subscription sidebar if you don't want subscription functionality:

```php
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

->recordActions([
    CommentsAction::make()
        ->mentionables(User::all())
        ->disableSidebar()
])
```

#### Subscription Methods

The `HasComments` trait provides methods for managing subscriptions programmatically:

```php
// Subscribe a user
$commentable->subscribe($user);

// Unsubscribe a user
$commentable->unsubscribe($user);

// Check if a user is subscribed
$isSubscribed = $commentable->isSubscribed($user);

// Get all subscribers
$subscribers = $commentable->getSubscribers();
```

***

### Configuration

You can publish the configuration file to make changes.

```bash
php artisan vendor:publish --tag="commentions-config"
```

#### Pagination (Filament)

Commentions supports built-in pagination for the embedded list of comments and it is enabled by default. You can disable it or control the number of comments shown per page and per click.

- Enabled by default
- Disable via `disablePagination()`
- Configure page size
- Customize the load more label
- Control how many comments are appended per click (defaults to the page size)

Examples:

Default Usage:

```php
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

->recordActions([
    CommentsAction::make()
        ->mentionables(User::all())
        ->perPage(10)
        
])
```
Without Pagination:

```php
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

->recordActions([
    CommentsAction::make()
        ->mentionables(User::all())
        ->disablePagination();
        
])
```
Advanced Usage:

```php
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;

Infolists\Components\Section::make('Comments')
    ->schema([
        CommentsEntry::make('comments')
            ->mentionables(fn (Model $record) => User::all())
            ->perPage(8)
            ->loadMoreIncrementsBy(8)
            ->loadMoreLabel('Show older'),
    ])
```

When pagination is enabled, a "Show more" button is displayed to load additional comments incrementally.

#### Configuring the User model and the mentionables

If your `User` model lives in a different namespace than `App\Models\User`, you can configure it in `config/commentions.php`:

```php
    'commenter' => [
        'model' => \App\Domains\Users\User::class,
    ],
```

#### Configuring the Comment model

If you need to customize the Comment model, you can extend the `\Kirschbaum\Commentions\Comment` class and then update the `comment.model` option in your `config/commentions.php` file:

```php
    'comment' => [
        'model' => \App\Models\Comment::class,
        // ...
    ],
```

#### Configuring Comment permissions

By default, users can create comments, as well as edit and delete their own comments. You can adjust these permissions by implementing your own policy:

##### 1) Create a custom policy

```php
namespace App\Policies;

use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Policies\CommentPolicy as CommentionsPolicy;

class CommentPolicy extends CommentionsPolicy
{
    public function create(Commenter $user): bool
    {
        // TODO: Implement custom permission logic.
    }

    public function update($user, Comment $comment): bool
    {
        // TODO: Implement custom permission logic.
    }

    public function delete($user, Comment $comment): bool
    {
        // TODO: Implement custom permission logic.
    }
}
```

##### 2) Register your policy in the configuration file

Update the `comment.policy` option in your `config/commentions.php` file:

```php
    'comment' => [
        // ...
        'policy' => \App\Policies\CommentPolicy::class,
    ],
```

### Configuring the Commenter name

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

### Configuring the Commenter avatar

To configure the avatar, make sure your User model implements Filament's `HasAvatar` interface.

```php
use Filament\Models\Contracts\HasAvatar;

class User extends Authenticatable implements Commenter, HasName, HasAvatar
{
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }
}
```

### Translations

You can publish the package translation files and override any strings used by the UI.

Publish the language files into your application:

```bash
php artisan vendor:publish --tag="commentions-lang"
``

This will copy the language files to:

- `lang/vendor/commentions/{locale}/comments.php`

Override only the keys you need. Example (English):

```php
// lang/vendor/commentions/en/comments.php
return [
    'label' => 'Notes',
    'no_comments_yet' => 'No notes yet.',
    'add_reaction' => 'Add a reaction',
    'cancel' => 'Close',
    'delete' => 'Remove',
    'save' => 'Update',
];
```

### Events

Events are dispatched when a comment is created, reacted to, or when users are mentioned or subscribed:

- `Kirschbaum\Commentions\Events\UserWasMentionedEvent`
- `Kirschbaum\Commentions\Events\UserIsSubscribedToCommentableEvent`
- `Kirschbaum\Commentions\Events\CommentWasCreatedEvent`
- `Kirschbaum\Commentions\Events\CommentWasReactedEvent`

#### Subscription Events

When a new comment is created, all subscribed users receive notifications through the `UserIsSubscribedToCommentableEvent`. You can listen to this event to send custom notifications:

```php
namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\NewCommentNotification;
use Kirschbaum\Commentions\Events\UserIsSubscribedToCommentableEvent;

class SendSubscribedUserNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserIsSubscribedToCommentableEvent $event): void
    {
        $event->user->notify(
            new NewCommentNotification($event->comment)
        );
    }
}
```

### Sending notifications when a user is mentioned

Every time a user is mentioned, the `Kirschbaum\Commentions\Events\UserWasMentionedEvent` is dispatched. Commentions ships an optional, opt-in notification you can enable via configuration, or you can listen to the event and handle it yourself.

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

#### Built-in opt-in notifications

Enable notifications for mentions in your `config/commentions.php`:

```php
    'notifications' => [
        'mentions' => [
            'enabled' => true,
            'channels' => ['mail', 'database'],
        ],
    ],
```

Optionally, provide a URL resolver so emails/links point users to the right place:

```php
use Kirschbaum\Commentions\Config;

Config::resolveCommentUrlUsing(function (\Kirschbaum\Commentions\Comment $comment) {
    // Return a URL to view the record and scroll to the comment
    return route('projects.show', $comment->commentable) . '#comment-' . $comment->getId();
});
```

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

Commentions supports polling for new comments. You can enable it on any component by calling the `poll` method and passing the desired interval.

```php
Infolists\Components\Section::make('Comments')
    ->schema([
        CommentsEntry::make('comments')
            ->poll('10s')
    ]),
```

### Rendering non-Comments in the list

Sometimes you might want to render non-Comments in the list of comments. For example, you might want to render when the status of a project is changed. For this, you can override the `getComments` method in your model, and return instances of the `Kirschbaum\Commentions\RenderableComment` data object.

```php
use Kirschbaum\Commentions\RenderableComment;

public function getComments(?int $limit = null): Collection
{
    $statusHistory = $this->statusHistory()->get()->map(fn (StatusHistory $statusHistory) => new RenderableComment(
        id: $statusHistory->id,
        authorName: $statusHistory->user->name,
        body: sprintf('Status changed from %s to %s', $statusHistory->old_status, $statusHistory->new_status),
        createdAt: $statusHistory->created_at,
    ));

    $comments = $this->comments()->latest()->with('author')->get();

    $mergedCollection = $statusHistory->merge($comments);

    if ($limit) {
        return $mergedCollection->take($limit);
    }

    return $mergedCollection;
}
```

***

## Security

If you discover any security related issues, please email security@kirschbaumdevelopment.com instead of using the issue tracker.

## Credits

- [Luis Dalmolin](https://github.com/luisdalmolin)
- [All contributors](https://github.com/kirschbaum-development/commentions/graphs/contributors)

## Sponsorship

Development of this package is sponsored by Kirschbaum Development Group, a developer driven company focused on problem solving, team building, and community. Learn more [about us](https://kirschbaumdevelopment.com?utm_source=github) or [join us](https://careers.kirschbaumdevelopment.com?utm_source=github)!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
