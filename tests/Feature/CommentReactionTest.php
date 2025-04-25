<?php

use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Comment as CommentComponent;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

test('user can add a reaction to a comment', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, ['comment' => $comment])
        ->call('toggleReaction', '👍');

    $this->assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);

    $comment->refresh();
    expect($comment->reactions)->toHaveCount(1);
    expect($comment->reactions->first()->reaction)->toBe('👍');
    expect($comment->reactions->first()->reactor->is($user))->toBeTrue();
});

test('user can remove their reaction from a comment', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    $reaction = $comment->reactions()->create([
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);

    $this->assertDatabaseHas('comment_reactions', [
        'id' => $reaction->id,
    ]);

    livewire(CommentComponent::class, ['comment' => $comment->fresh('reactions')])
        ->call('toggleReaction', '👍');

    $this->assertDatabaseMissing('comment_reactions', [
        'id' => $reaction->id,
    ]);

    $comment->refresh();
    expect($comment->reactions)->toHaveCount(0);
});

test('user cannot react twice with the same reaction via toggle', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    $livewire = livewire(CommentComponent::class, ['comment' => $comment]);

    $livewire->call('toggleReaction', '👍');

    $this->assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reaction' => '👍',
    ]);
    expect($comment->refresh()->reactions)->toHaveCount(1);

    $livewire->call('toggleReaction', '👍');

    $this->assertDatabaseMissing('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reaction' => '👍',
    ]);
    expect($comment->refresh()->reactions)->toHaveCount(0);
});

test('multiple users can react to the same comment', function () {
    /** @var User $user1 */
    $user1 = User::factory()->create();
    /** @var User $user2 */
    $user2 = User::factory()->create();

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user1)->commentable($post)->create();

    // User 1 reacts
    actingAs($user1);
    livewire(CommentComponent::class, ['comment' => $comment])
        ->call('toggleReaction', '👍');

    // User 2 reacts
    actingAs($user2);
    livewire(CommentComponent::class, ['comment' => $comment->fresh('reactions')])
        ->call('toggleReaction', '❤️');

    // User 2 also reacts with thumbs up
    livewire(CommentComponent::class, ['comment' => $comment->fresh('reactions')])
        ->call('toggleReaction', '👍');

    $this->assertDatabaseHas('comment_reactions', ['comment_id' => $comment->id, 'reactor_id' => $user1->id, 'reaction' => '👍']);
    $this->assertDatabaseHas('comment_reactions', ['comment_id' => $comment->id, 'reactor_id' => $user2->id, 'reaction' => '❤️']);
    $this->assertDatabaseHas('comment_reactions', ['comment_id' => $comment->id, 'reactor_id' => $user2->id, 'reaction' => '👍']);

    expect($comment->refresh()->reactions)->toHaveCount(3);
});

test('unauthenticated user cannot react', function () {
    /** @var User $user */
    $user = User::factory()->create(); // Author
    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    Config::resolveAuthenticatedUserUsing(fn () => null);
    Auth::logout();

    livewire(CommentComponent::class, ['comment' => $comment])
        ->call('toggleReaction', '👍');

    $this->assertDatabaseMissing('comment_reactions', [
        'comment_id' => $comment->id,
        'reaction' => '👍',
    ]);
});

test('reaction display updates correctly via computed property', function () {
    /** @var User $user1 */
    $user1 = User::factory()->create();
    /** @var User $user2 */
    $user2 = User::factory()->create();
    actingAs($user1);

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user1)->commentable($post)->create();

    $component = livewire(CommentComponent::class, ['comment' => $comment]);

    $component->assertDontSeeHtml('<span wire:key="reaction-count-thumbs-up-' . $comment->getId() . '">1</span>');
    expect($component->get('reactionSummary'))->toBeEmpty();

    $component->call('toggleReaction', '👍');
    $component->assertSeeHtml('<span>👍</span>');
    $summary = $component->get('reactionSummary');
    expect($summary)->toHaveKey('👍');
    expect($summary['👍']['count'])->toBe(1);
    expect($summary['👍']['reacted_by_current_user'])->toBeTrue();

    // User 2 adds reaction (simulate this by directly creating the reaction for simplicity in test)
    $comment->reactions()->create([
        'reactor_id' => $user2->getKey(),
        'reactor_type' => $user2->getMorphClass(),
        'reaction' => '👍',
    ]);

    $component->set('comment', $comment->fresh('reactions'));
    $summary = $component->get('reactionSummary');
    expect($summary['👍']['count'])->toBe(2);
    expect($summary['👍']['reacted_by_current_user'])->toBeTrue(); // Still true for user 1
    $component->assertSeeHtml('<span wire:key="reaction-count-thumbs-up-' . $comment->getId() . '">2</span>'); // Count should now be 2

    // User 1 removes reaction
    $component->call('toggleReaction', '👍');
    $summary = $component->get('reactionSummary');
    expect($summary['👍']['count'])->toBe(1);
    expect($summary['👍']['reacted_by_current_user'])->toBeFalse(); // False now for user 1
});
