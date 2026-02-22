<?php

use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Comment as CommentComponent;
use Kirschbaum\Commentions\RenderableComment;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

test('can render a comment', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment body',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->assertSee('Test comment body')
        ->assertSee($comment->author->name)
        ->assertSeeHtml('wire:click="edit"')  // Author should see an edit button
        ->assertSeeHtml('wire:click="delete"'); // Author should see a delete button
});

test('other users cannot see edit and delete buttons by default', function () {
    $user = User::factory()->create();
    actingAs($user);

    $author = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($author)->commentable($post)->create();

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->assertDontSeeHtml('wire:click="edit"')
        ->assertDontSeeHtml('wire:click="delete"');
});

test('guests cannot see edit and delete buttons', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($author)->commentable($post)->create();

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->assertDontSeeHtml('wire:click="edit"')
        ->assertDontSeeHtml('wire:click="delete"');
});

test('custom policy can change who can see edit and delete buttons', function () {
    $user = User::factory()->create();
    actingAs($user);

    \Gate::policy(Comment::class, \Tests\Policies\BlockedCommentPolicy::class);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->assertDontSeeHtml('wire:click="edit"')
        ->assertDontSeeHtml('wire:click="delete"');
});

test('author can update a comment by default', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment body',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->set('commentBody', 'Updated comment body')
        ->call('updateComment');

    test()->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => 'Updated comment body',
    ]);
});

test('other users cannot update a comment by default', function () {
    $user = User::factory()->create();
    actingAs($user);

    $author = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($author)->commentable($post)->create([
        'body' => 'Test comment body',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->set('commentBody', 'Updated comment body')
        ->call('updateComment');

    test()->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => 'Test comment body',
    ]);
});

test('guests cannot update a comment', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($author)->commentable($post)->create([
        'body' => 'Test comment body',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->set('commentBody', 'Updated comment body')
        ->call('updateComment');

    test()->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => 'Test comment body',
    ]);
});

test('custom policy can change who can edit a comment', function () {
    $user = User::factory()->create();
    actingAs($user);

    \Gate::policy(Comment::class, \Tests\Policies\BlockedCommentPolicy::class);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment body',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->set('commentBody', 'Updated comment body')
        ->call('updateComment');

    test()->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => 'Test comment body',
    ]);
});

test('author can delete a comment by default', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])->call('delete');

    test()->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
});

test('other users cannot delete a comment by default', function () {
    $user = User::factory()->create();
    actingAs($user);

    $author = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($author)->commentable($post)->create();

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])->call('delete');

    test()->assertDatabaseHas('comments', [
        'id' => $comment->id,
    ]);
});

test('guests cannot delete a comment', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($author)->commentable($post)->create();

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])->call('delete');

    test()->assertDatabaseHas('comments', [
        'id' => $comment->id,
    ]);
});

test('custom policy can change who can delete a comment', function () {
    $user = User::factory()->create();
    actingAs($user);

    \Gate::policy(Comment::class, \Tests\Policies\BlockedCommentPolicy::class);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])->call('delete');

    test()->assertDatabaseHas('comments', [
        'id' => $comment->id,
    ]);
});

test('editing comment editor includes prefixed component alias', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    $componentAlias = Config::getComponentPrefix() . 'comment';

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->call('edit')
        ->assertSee($componentAlias, false);
});

test('can render a custom renderable comment', function () {
    $comment = new RenderableComment(
        id: 1,
        authorName: 'System',
        body: 'System notification'
    );

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->assertSee('System notification')
        ->assertSee('System')
        ->assertDontSeeHtml('wire:click="edit"')  // Should not show edit button
        ->assertDontSeeHtml('wire:click="delete"'); // Should not show delete button
});
