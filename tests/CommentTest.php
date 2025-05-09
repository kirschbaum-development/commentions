<?php

namespace Tests;

use Illuminate\Support\Facades\Event;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;
use Tests\Models\Post;
use Tests\Models\User;

test('it can save a comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = $post->comment('This is a test comment', $user);

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->body->toBe('This is a test comment')
        ->author->toBeModel($user)
        ->commentable->toBeModel($post);

    expect($post->comments)->toHaveCount(1);
});

test('it cannot save a comment when the policy denies it', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    \Gate::policy(Comment::class, \Tests\Policies\BlockedCommentPolicy::class);

    expect(fn () => $post->comment('This is a test comment', $user))
        ->toThrow(\Exception::class)
        ->and($post->comments)->toHaveCount(0);
});

test('it dispatches events for mentions', function () {
    Event::fake();

    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $post = Post::factory()->create();

    $comment = $post->comment(
        sprintf('Hey <span data-type="mention" data-id="%s">@%s</span>', $anotherUser->id, $anotherUser->name),
        $user
    );

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->body->toContain('Hey')
        ->author->toBeModel($user)
        ->commentable->toBeModel($post);

    expect($post->comments)->toHaveCount(1);

    Event::assertDispatched(UserWasMentionedEvent::class, function ($event) use ($anotherUser) {
        return $event->user->is($anotherUser);
    });
});

test('it can get mentioned user ids from comment', function () {
    $user = User::factory()->create();
    $mentionedUser1 = User::factory()->create();
    $mentionedUser2 = User::factory()->create();

    $comment = new Comment([
        'body' => sprintf(
            'Hey <span data-type="mention" data-id="%s">@%s</span> and <span data-type="mention" data-id="%s">@%s</span>',
            $mentionedUser1->id,
            $mentionedUser1->name,
            $mentionedUser2->id,
            $mentionedUser2->name
        ),
    ]);

    expect($comment->getMentioned())
        ->toHaveCount(2)
        ->toContain($mentionedUser1)
        ->toContain($mentionedUser2);
});
