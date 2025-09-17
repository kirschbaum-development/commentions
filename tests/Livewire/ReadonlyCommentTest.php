<?php

use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Livewire\Comment as CommentComponent;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

test('readonly comment component hides edit and delete buttons', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment body',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => true,
    ])
        ->assertSee('Test comment body')
        ->assertSee($comment->author->name)
        ->assertDontSeeHtml('wire:click="edit"')  // Edit button should be hidden
        ->assertDontSeeHtml('wire:click="delete"'); // Delete button should be hidden
});

test('readonly comment component prevents editing', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Original comment body',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => true,
    ])
        ->call('edit')
        ->assertSet('editing', false) // Should not enter edit mode
        ->assertSet('commentBody', ''); // Comment body should remain empty
});

test('readonly comment component prevents deleting', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Comment to be protected',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => true,
    ])
        ->call('delete');

    // Comment should still exist in database
    assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => 'Comment to be protected',
    ]);
});

test('readonly comment component prevents updating', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Original comment body',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => true,
    ])
        ->set('commentBody', 'Modified comment body')
        ->call('updateComment');

    // Comment should remain unchanged in database
    assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => 'Original comment body',
    ]);

    assertDatabaseMissing('comments', [
        'id' => $comment->id,
        'body' => 'Modified comment body',
    ]);
});

test('readonly comment component isReadonly method returns correct value', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    // Test readonly true
    $readonlyComponent = livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => true,
    ]);

    expect($readonlyComponent->instance()->isReadonly())->toBeTrue();

    // Test readonly false
    $editableComponent = livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => false,
    ]);

    expect($editableComponent->instance()->isReadonly())->toBeFalse();

    // Test default value
    $defaultComponent = livewire(CommentComponent::class, [
        'comment' => $comment,
    ]);

    expect($defaultComponent->instance()->isReadonly())->toBeFalse();
});

test('non-readonly comment component allows editing and deleting', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Original comment body',
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
        'readonly' => false,
    ])
        ->assertSeeHtml('wire:click="edit"')  // Edit button should be visible
        ->assertSeeHtml('wire:click="delete"') // Delete button should be visible
        ->call('edit')
        ->assertSet('editing', true) // Should enter edit mode
        ->assertSet('commentBody', 'Original comment body'); // Comment body should be loaded
});
