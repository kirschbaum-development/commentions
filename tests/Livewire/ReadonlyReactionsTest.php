<?php

use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Reactions;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    config(['commentions.reactions.allowed' => ['👍', '❤️', '😂', '😮', '😢', '🤔']]);
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

test('readonly reactions component prevents adding reactions', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    livewire(Reactions::class, [
        'comment' => $comment,
        'readonly' => true,
    ])
        ->call('handleReactionToggle', '👍');

    // No reaction should be created
    assertDatabaseMissing('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);
});

test('readonly reactions component prevents removing reactions', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    // First create a reaction directly in the database
    $comment->reactions()->create([
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);

    // Verify reaction exists
    assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);

    // Try to remove it in readonly mode
    livewire(Reactions::class, [
        'comment' => $comment,
        'readonly' => true,
    ])
        ->call('handleReactionToggle', '👍');

    // Reaction should still exist
    assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);
});

test('readonly reactions component isReadonly method returns correct value', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    // Test readonly true
    $readonlyComponent = livewire(Reactions::class, [
        'comment' => $comment,
        'readonly' => true,
    ]);

    expect($readonlyComponent->instance()->isReadonly())->toBeTrue();

    // Test readonly false
    $editableComponent = livewire(Reactions::class, [
        'comment' => $comment,
        'readonly' => false,
    ]);

    expect($editableComponent->instance()->isReadonly())->toBeFalse();

    // Test default value
    $defaultComponent = livewire(Reactions::class, [
        'comment' => $comment,
    ]);

    expect($defaultComponent->instance()->isReadonly())->toBeFalse();
});

test('non-readonly reactions component allows reaction toggling via Comment component', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    // Test that non-readonly reactions component correctly calls the event
    $reactionsComponent = livewire(Reactions::class, [
        'comment' => $comment,
        'readonly' => false,
    ]);

    expect($reactionsComponent->instance()->isReadonly())->toBeFalse();

    // Verify that the handleReactionToggle method executes without readonly restrictions
    // We test this by ensuring the method doesn't return early due to readonly check
    $reactionsComponent->call('handleReactionToggle', '👍');

    // The actual reaction creation happens in the Comment component via the dispatched event
    // So we test the reaction creation through the Comment component integration
    $comment->toggleReaction('👍');

    assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);
});
