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

test('a top-level comment renders as a bordered card', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Comment::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, ['comment' => $comment, 'depth' => 0])
        ->assertSeeHtml('comm:rounded-lg')
        ->assertSeeHtml('comm:shadow-sm')
        ->assertDontSeeHtml('commentions-thread');
});

test('a reply renders as a flat row with a thread connector', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $parent = Comment::factory()->author($user)->commentable($post)->create();
    $reply = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $parent->id]);

    livewire(CommentComponent::class, ['comment' => $reply, 'depth' => 1])
        ->assertDontSeeHtml('comm:shadow-sm')
        ->assertSeeHtml('comm:py-2')
        ->assertSeeHtml('comm:w-7')
        ->assertSeeHtml('class="commentions-thread"');
});

test('a comment with replies renders an accessible collapse toggle', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $parent = Comment::factory()->author($user)->commentable($post)->create();
    Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $parent->id]);

    livewire(CommentComponent::class, ['comment' => $parent, 'depth' => 0])
        ->assertSeeHtml(':aria-expanded')
        ->assertSeeHtml('aria-controls="comment-replies-' . $parent->id . '"')
        ->assertSeeHtml('id="comment-replies-' . $parent->id . '"')
        ->assertSeeHtml('role="group"');
});

test('the collapse toggle shows the total descendant reply count', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $parent = Comment::factory()->author($user)->commentable($post)->create();
    $child = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $parent->id]);
    Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $child->id]);

    livewire(CommentComponent::class, ['comment' => $parent, 'depth' => 0])
        ->assertSee('2 replies');
});

test('a comment with no replies renders no collapse toggle', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Comment::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, ['comment' => $comment, 'depth' => 0])
        ->assertDontSeeHtml('aria-controls="comment-replies-');
});

test('replies indent for the first levels then stop', function () {
    config(['commentions.threading.max_depth' => 5]);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $c0 = Comment::factory()->author($user)->commentable($post)->create();
    $c1 = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $c0->id]);
    Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $c1->id]);

    // A depth-0 wrapper indents the replies it renders.
    livewire(CommentComponent::class, ['comment' => $c0, 'depth' => 0])
        ->assertSeeHtml('comm:pl-3');

    // A wrapper at INDENT_CAP_DEPTH stops adding indent.
    livewire(CommentComponent::class, ['comment' => $c1, 'depth' => CommentComponent::INDENT_CAP_DEPTH])
        ->assertDontSeeHtml('comm:pl-3');
});

test('repliesCount counts every descendant comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $root = Comment::factory()->author($user)->commentable($post)->create();
    $a = Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $root->id]);
    Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $a->id]);
    Comment::factory()->author($user)->commentable($post)->create(['parent_id' => $root->id]);

    expect($root->fresh()->repliesCount())->toBe(3);
});
