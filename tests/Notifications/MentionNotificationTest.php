<?php

use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;
use Kirschbaum\Commentions\Listeners\SendUserMentionedNotification;
use Kirschbaum\Commentions\Notifications\UserMentionedInComment;
use Tests\Models\Post;
use Tests\Models\User;

test('it sends a mention notification when enabled with configured channels', function () {
    config()->set('commentions.notifications.mentions.enabled', true);
    config()->set('commentions.notifications.mentions.channels', ['mail', 'database']);

    /** @var User $author */
    $author = User::factory()->create();
    /** @var User $mentioned */
    $mentioned = User::factory()->create();
    /** @var Post $post */
    $post = Post::factory()->create();

    $comment = $post->comment(
        sprintf('Hey <span data-type="mention" data-id="%s">@%s</span>', $mentioned->id, $mentioned->name),
        $author
    );

    NotificationFacade::fake();

    $listener = app(SendUserMentionedNotification::class);
    $listener->handle(new UserWasMentionedEvent($comment, $mentioned));

    NotificationFacade::assertSentTo(
        $mentioned,
        UserMentionedInComment::class,
        function (UserMentionedInComment $notification, array $channels) use ($comment) {
            expect($channels)->toEqualCanonicalizing(['mail', 'database']);

            $payload = $notification->toArray($comment->author);
            expect($payload['comment_id'])->toBe($comment->getId());
            expect($payload['author_name'])->toBe($comment->getAuthorName());

            return true;
        }
    );
});

class TestCustomMentionNotification extends BaseNotification
{
    public function __construct(public Comment $comment, public array $channels) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'custom' => true,
            'comment_id' => $this->comment->getId(),
        ];
    }
}

test('it allows overriding the notification class via config', function () {
    config()->set('commentions.notifications.mentions.enabled', true);
    config()->set('commentions.notifications.mentions.channels', ['mail']);
    config()->set('commentions.notifications.mentions.notification', TestCustomMentionNotification::class);

    /** @var User $author */
    $author = User::factory()->create();
    /** @var User $mentioned */
    $mentioned = User::factory()->create();
    /** @var Post $post */
    $post = Post::factory()->create();

    $comment = $post->comment('Hello world', $author);

    NotificationFacade::fake();

    $listener = app(SendUserMentionedNotification::class);
    $listener->handle(new UserWasMentionedEvent($comment, $mentioned));

    NotificationFacade::assertSentTo(
        $mentioned,
        TestCustomMentionNotification::class,
        function (TestCustomMentionNotification $notification) use ($comment) {
            $payload = $notification->toArray($comment->author);
            expect($payload['custom'])->toBeTrue();
            expect($payload['comment_id'])->toBe($comment->getId());

            return true;
        }
    );
});

test('it does not send a mention notification when disabled', function () {
    config()->set('commentions.notifications.mentions.enabled', false);
    config()->set('commentions.notifications.mentions.channels', ['mail']);

    /** @var User $author */
    $author = User::factory()->create();
    /** @var User $mentioned */
    $mentioned = User::factory()->create();
    /** @var Post $post */
    $post = Post::factory()->create();

    $comment = $post->comment('Hello world', $author);

    NotificationFacade::fake();

    $listener = app(SendUserMentionedNotification::class);
    $listener->handle(new UserWasMentionedEvent($comment, $mentioned));

    NotificationFacade::assertNothingSent();
});

test('it does not send a mention notification when channels are empty', function () {
    config()->set('commentions.notifications.mentions.enabled', true);
    config()->set('commentions.notifications.mentions.channels', []);

    /** @var User $author */
    $author = User::factory()->create();
    /** @var User $mentioned */
    $mentioned = User::factory()->create();
    /** @var Post $post */
    $post = Post::factory()->create();

    $comment = $post->comment('Hello world', $author);

    NotificationFacade::fake();

    $listener = app(SendUserMentionedNotification::class);
    $listener->handle(new UserWasMentionedEvent($comment, $mentioned));

    NotificationFacade::assertNothingSent();
});

test('it uses the configured comment URL resolver in notification payload', function () {
    config()->set('commentions.notifications.mentions.enabled', true);
    config()->set('commentions.notifications.mentions.channels', ['database']);

    $resolvedUrl = 'https://example.test/some-resource#comment-123';
    Config::resolveCommentUrlUsing(function (Comment $comment) use ($resolvedUrl) {
        return $resolvedUrl;
    });

    /** @var User $author */
    $author = User::factory()->create();
    /** @var User $mentioned */
    $mentioned = User::factory()->create();
    /** @var Post $post */
    $post = Post::factory()->create();

    $comment = $post->comment('Hello world', $author);

    NotificationFacade::fake();

    $listener = app(SendUserMentionedNotification::class);
    $listener->handle(new UserWasMentionedEvent($comment, $mentioned));

    NotificationFacade::assertSentTo(
        $mentioned,
        UserMentionedInComment::class,
        function (UserMentionedInComment $notification) use ($resolvedUrl, $mentioned) {
            $payload = $notification->toArray($mentioned);
            expect($payload['url'])->toBe($resolvedUrl);

            return true;
        }
    );
});
