<?php

use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Comment as CommentComponent;
use Kirschbaum\Commentions\Livewire\CommentList;
use Kirschbaum\Commentions\Livewire\Comments;
use Kirschbaum\Commentions\Livewire\Reactions;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    config(['commentions.reactions.allowed' => ['👍', '❤️', '😂']]);
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

test('full readonly flow prevents all interactions', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $existingComment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Existing comment',
    ]);
    // Create a reaction directly in the database
    $existingComment->reactions()->create([
        'reactor_id' => $user->id,
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);

    // Test Comments component in readonly mode
    $commentsComponent = livewire(Comments::class, [
        'record' => $post,
        'readonly' => true,
    ]);

    // Should not allow creating new comments
    $commentsComponent
        ->set('commentBody', 'New comment attempt')
        ->call('save')
        ->assertSet('commentBody', 'New comment attempt'); // Should not be cleared

    assertDatabaseMissing('comments', [
        'body' => 'New comment attempt',
    ]);

    // Test Comment component in readonly mode
    $commentComponent = livewire(CommentComponent::class, [
        'comment' => $existingComment,
        'readonly' => true,
    ]);

    // Should not allow editing
    $commentComponent
        ->call('edit')
        ->assertSet('editing', false);

    // Should not allow deleting
    $commentComponent->call('delete');
    assertDatabaseHas('comments', [
        'id' => $existingComment->id,
        'body' => 'Existing comment',
    ]);

    // Test Reactions component in readonly mode
    $reactionsComponent = livewire(Reactions::class, [
        'comment' => $existingComment,
        'readonly' => true,
    ]);

    // Should not allow adding new reactions
    $reactionsComponent->call('handleReactionToggle', '❤️');
    assertDatabaseMissing('comment_reactions', [
        'comment_id' => $existingComment->id,
        'reaction' => '❤️',
    ]);

    // Should not allow removing existing reactions
    $reactionsComponent->call('handleReactionToggle', '👍');
    assertDatabaseHas('comment_reactions', [
        'comment_id' => $existingComment->id,
        'reaction' => '👍',
    ]);
});

test('readonly state is properly inherited through component hierarchy', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment',
    ]);

    // Test that CommentList passes readonly to child components
    $commentListComponent = livewire(CommentList::class, [
        'record' => $post,
        'readonly' => true,
    ]);

    expect($commentListComponent->instance()->isReadonly())->toBeTrue();

    // Test Comments component with sidebar
    $commentsComponent = livewire(Comments::class, [
        'record' => $post,
        'readonly' => true,
        'sidebarEnabled' => true,
    ]);

    expect($commentsComponent->instance()->isReadonly())->toBeTrue();
});

test('mixed readonly and editable components work independently', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment',
    ]);

    // One readonly component
    $readonlyComponent = livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => true,
    ]);

    // One editable component
    $editableComponent = livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => false,
    ]);

    // Readonly should prevent editing
    $readonlyComponent
        ->call('edit')
        ->assertSet('editing', false);

    // Editable should allow editing
    $editableComponent
        ->call('edit')
        ->assertSet('editing', true);

    expect($readonlyComponent->instance()->isReadonly())->toBeTrue();
    expect($editableComponent->instance()->isReadonly())->toBeFalse();
});

test('readonly mode respects existing permissions', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();
    actingAs($otherUser);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($author)->commentable($post)->create([
        'body' => 'Authors comment',
    ]);

    // Even in non-readonly mode, other users shouldn't be able to edit
    $component = livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => false,
    ]);

    // Should not show edit/delete buttons due to permissions
    $component
        ->assertDontSeeHtml('wire:click="edit"')
        ->assertDontSeeHtml('wire:click="delete"');

    // In readonly mode, still shouldn't be able to edit
    $readonlyComponent = livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => true,
    ]);

    $readonlyComponent
        ->assertDontSeeHtml('wire:click="edit"')
        ->assertDontSeeHtml('wire:click="delete"');
});
