<?php

use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Comment as CommentComponent;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
    config(['commentions.threading.enabled' => true]);
});

test('a comment can be created as a reply to another comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $parent = Comment::factory()->author($user)->commentable($post)->create();

    $reply = SaveComment::run($post, $user, 'A reply', $parent->id);

    expect($reply->parent_id)->toBe($parent->id)
        ->and($parent->replies()->count())->toBe(1)
        ->and($reply->parent->is($parent))->toBeTrue();
});

test('comment depth reflects nesting', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $a = Comment::factory()->author($user)->commentable($post)->create();
    $b = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $a->id]);
    $c = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $b->id]);

    expect($a->depth())->toBe(0)
        ->and($b->depth())->toBe(1)
        ->and($c->depth())->toBe(2);
});

test('getComments returns only top-level comments when threading is enabled', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $parent = Comment::factory()->author($user)->commentable($post)->create();
    Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $parent->id]);

    expect($post->getComments())->toHaveCount(1)
        ->and($post->getComments()->first()->replies)->toHaveCount(1);
});

test('getComments returns all comments flat when threading is disabled', function () {
    config(['commentions.threading.enabled' => false]);

    $user = User::factory()->create();
    $post = Post::factory()->create();

    $parent = Comment::factory()->author($user)->commentable($post)->create();
    Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $parent->id]);

    expect($post->getComments())->toHaveCount(2);
});

test('the reply control saves a nested reply', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $parent = Comment::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, ['comment' => $parent])
        ->call('reply')
        ->assertSet('replying', true)
        ->set('commentBody', 'My threaded reply')
        ->call('saveReply')
        ->assertSet('replying', false);

    expect($parent->replies()->count())->toBe(1)
        ->and($parent->replies()->first()->body)->toBe('My threaded reply');
});

test('the reply control is hidden when threading is disabled', function () {
    config(['commentions.threading.enabled' => false]);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Comment::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertDontSeeHtml('wire:click="reply"');
});

test('the reply control is hidden once the max depth is reached', function () {
    config(['commentions.threading.max_depth' => 1]);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $parent = Comment::factory()->author($user)->commentable($post)->create();
    $reply = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $parent->id]);

    livewire(CommentComponent::class, ['comment' => $parent, 'depth' => 0])
        ->assertSeeHtml('wire:click="reply"');

    livewire(CommentComponent::class, ['comment' => $reply, 'depth' => 1])
        ->assertDontSeeHtml('wire:click="reply"');
});

test('replies beyond the max depth are not saved', function () {
    config(['commentions.threading.max_depth' => 1]);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $parent = Comment::factory()->author($user)->commentable($post)->create();
    $reply = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $parent->id]);

    livewire(CommentComponent::class, ['comment' => $reply, 'depth' => 1])
        ->set('commentBody', 'too deep')
        ->call('saveReply');

    expect($reply->replies()->count())->toBe(0);
});

test('deleting a comment deletes its nested replies', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $parent = Comment::factory()->author($user)->commentable($post)->create();
    $reply = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $parent->id]);
    $nested = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $reply->id]);

    $parent->delete();

    test()->assertDatabaseMissing('comments', ['id' => $reply->id]);
    test()->assertDatabaseMissing('comments', ['id' => $nested->id]);
});
