<?php

use Kirschbaum\Commentions\Comment as CommentModel;
use Tests\Models\Post;
use Tests\Models\StubAvatarProvider;
use Tests\Models\User;

test('falls back to ui-avatars when no provider is configured', function () {
    config()->set('commentions.avatar_provider', null);

    $user = User::factory()->create(['name' => 'Jane Doe']);
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    expect($comment->getAuthorAvatar())
        ->toStartWith('https://ui-avatars.com/api/?');
});

test('uses configured avatar provider when set', function () {
    config()->set('commentions.avatar_provider', StubAvatarProvider::class);

    $user = User::factory()->create(['name' => 'Jane Doe']);
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    expect($comment->getAuthorAvatar())
        ->toBe('https://stub.test/avatar/Jane+Doe');
});
