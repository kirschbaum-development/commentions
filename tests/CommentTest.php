<?php

namespace Tests;

use Illuminate\Support\Facades\Event;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\CommentionsPlugin;
use Kirschbaum\Commentions\Config;
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

test('it can disable editing of comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = $post->comment('This is a test comment', $user);

    // Set authenticated user to the comment author
    Config::resolveAuthenticatedUserUsing(fn () => $user);

    // Should be able to edit by default
    expect($comment->canEdit())->toBeTrue();

    // Disable edits
    config(['commentions.allow_edits' => false]);
    expect($comment->canEdit())->toBeFalse();
});

test('it can disable deletion of comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = $post->comment('This is a test comment', $user);

    // Set authenticated user to the comment author
    Config::resolveAuthenticatedUserUsing(fn () => $user);

    // Should be able to delete by default
    expect($comment->canDelete())->toBeTrue();

    // Disable deletes
    config(['commentions.allow_deletes' => false]);
    expect($comment->canDelete())->toBeFalse();
});

test('plugin can disallow edits and deletes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = $post->comment('This is a test comment', $user);

    // Set authenticated user to the comment author
    Config::resolveAuthenticatedUserUsing(fn () => $user);

    // Reset to default values
    Config::allowEdits(true);
    Config::allowDeletes(true);

    // Should be able to edit and delete by default
    expect($comment->canEdit())->toBeTrue();
    expect($comment->canDelete())->toBeTrue();

    // Create a plugin instance and disallow edits
    $plugin = new CommentionsPlugin();
    $plugin->disallowEdits();
    $panel = new \Filament\Panel();
    $plugin->register($panel);

    expect($comment->canEdit())->toBeFalse();
    expect($comment->canDelete())->toBeTrue();

    // Reset and test disallowing deletes
    Config::allowEdits(true);
    Config::allowDeletes(true);

    $plugin = new CommentionsPlugin();
    $plugin->disallowDeletes();
    $plugin->register($panel);

    expect($comment->canEdit())->toBeTrue();
    expect($comment->canDelete())->toBeFalse();

    // Test disallowing both
    Config::allowEdits(true);
    Config::allowDeletes(true);

    $plugin = new CommentionsPlugin();
    $plugin->disallowEdits()->disallowDeletes();
    $plugin->register($panel);

    expect($comment->canEdit())->toBeFalse();
    expect($comment->canDelete())->toBeFalse();
});
