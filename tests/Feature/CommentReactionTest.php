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
    config(['commentions.allowed_reactions' => ['👍', '❤️']]);
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

test('user can add configured reactions to a comment', function (string $reactionEmoji) {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, ['comment' => $comment])
        ->call('toggleReaction', $reactionEmoji);

    $this->assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => $reactionEmoji,
    ]);

    $comment->refresh();
    expect($comment->reactions)->toHaveCount(1);
    expect($comment->reactions->first()->reaction)->toBe($reactionEmoji);
})->with(['👍', '❤️']);

test('user can remove their reaction from a comment', function (string $reactionEmoji) {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    // Add reaction first
    $comment->reactions()->create([
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => $reactionEmoji,
    ]);

    $this->assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reaction' => $reactionEmoji,
    ]);

    // Toggle to remove
    livewire(CommentComponent::class, ['comment' => $comment])
        ->call('toggleReaction', $reactionEmoji);

    $this->assertDatabaseMissing('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reaction' => $reactionEmoji,
    ]);
    expect($comment->refresh()->reactions)->toHaveCount(0);
})->with(['👍', '❤️']);

test('user cannot add a non-configured reaction via toggleReaction', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    $nonConfiguredReaction = '🤔'; // Assuming this is not in the default test config

    livewire(CommentComponent::class, ['comment' => $comment])
        ->call('toggleReaction', $nonConfiguredReaction); // Attempt to add

    $this->assertDatabaseMissing('comment_reactions', [ // Should not be saved
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reaction' => $nonConfiguredReaction,
    ]);
    expect($comment->refresh()->reactions)->toHaveCount(0);
});

test('reaction summary handles multiple different reactions', function () {
    /** @var User $user1 */
    $user1 = User::factory()->create();
    /** @var User $user2 */
    $user2 = User::factory()->create();
    /** @var User $user3 */
    $user3 = User::factory()->create();
    actingAs($user1); // Current user for component context

    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user1)->commentable($post)->create();

    // Add some reactions directly for setup
    $comment->reactions()->create(['reactor_id' => $user1->id, 'reactor_type' => $user1->getMorphClass(), 'reaction' => '👍']);
    $comment->reactions()->create(['reactor_id' => $user2->id, 'reactor_type' => $user2->getMorphClass(), 'reaction' => '👍']);
    $comment->reactions()->create(['reactor_id' => $user3->id, 'reactor_type' => $user3->getMorphClass(), 'reaction' => '❤️']);

    $component = livewire(CommentComponent::class, ['comment' => $comment->fresh('reactions')]);

    $summary = $component->get('reactionSummary');

    expect($summary)->toBeArray()
        ->toHaveKeys(['👍', '❤️'])
        ->and($summary['👍']['count'])->toBe(2)
        ->and($summary['👍']['reacted_by_current_user'])->toBeTrue() // User1 reacted with 👍
        ->and($summary['❤️']['count'])->toBe(1)
        ->and($summary['❤️']['reacted_by_current_user'])->toBeFalse(); // User1 did not react with ❤️

    // Check Blade rendering simulation (simplified)
    $component
        ->assertSeeHtml('wire:key="reaction-count-👍-'.$comment->getId().'"') // Count exists for 👍
        ->assertSeeHtml('>2</span>') // Correct count for 👍
        ->assertSeeHtml('wire:key="reaction-count-❤️-'.$comment->getId().'"') // Count exists for ❤️
        ->assertSeeHtml('>1</span>'); // Correct count for ❤️
});

test('guest cannot add reactions', function (string $reactionEmoji) {
    /** @var User $user */
    $user = User::factory()->create();
    $post = Post::factory()->create();
    /** @var CommentModel $comment */
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, ['comment' => $comment])
        ->call('toggleReaction', $reactionEmoji);

    $this->assertDatabaseMissing('comment_reactions', [
        'comment_id' => $comment->id,
        'reaction' => $reactionEmoji,
    ]);
})->with(['👍', '❤️']);
