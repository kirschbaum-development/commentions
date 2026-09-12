<?php

use Kirschbaum\Commentions\Comment as CommentModel;
use Tests\Models\Post;
use Tests\Models\User;
use Tests\Models\UserWithCommenterName;
use Tests\Models\UserWithFilamentName;

test('getAuthorName uses the author name property by default', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    expect($comment->getAuthorName())->toBe('Jane Doe');
});

test('getAuthorName uses getCommenterName when the author defines it', function () {
    $user = UserWithCommenterName::query()->find(
        User::factory()->create(['name' => 'Jane Doe'])->id,
    );
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    expect($comment->getAuthorName())->toBe('Commenter #' . $user->id)
        ->and($comment->author->name)->toBe('Jane Doe');
});

test('getAuthorName uses Filament HasName when the author implements it', function () {
    $user = UserWithFilamentName::query()->find(
        User::factory()->create(['name' => 'Jane Doe'])->id,
    );
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    expect($comment->getAuthorName())->toBe('Filament #' . $user->id)
        ->and($comment->author->name)->toBe('Jane Doe');
});

test('getAuthorName falls back when the author has been deleted', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    $user->delete();
    $comment->refresh();

    expect($comment->author)->toBeNull()
        ->and($comment->getAuthorName())->toBe(__('commentions::comments.deleted_user'));
});

test('getAuthorAvatar does not throw when the author has been deleted', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    $user->delete();
    $comment->refresh();

    expect($comment->getAuthorAvatar())
        ->toStartWith('https://ui-avatars.com/api/?');
});
