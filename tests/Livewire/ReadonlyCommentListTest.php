<?php

use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Livewire\CommentList;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

test('readonly comment list component isReadonly method returns correct value', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    // Test readonly true
    $readonlyComponent = livewire(CommentList::class, [
        'record' => $post,
        'readonly' => true,
    ]);

    expect($readonlyComponent->instance()->isReadonly())->toBeTrue();

    // Test readonly false
    $editableComponent = livewire(CommentList::class, [
        'record' => $post,
        'readonly' => false,
    ]);

    expect($editableComponent->instance()->isReadonly())->toBeFalse();

    // Test default value
    $defaultComponent = livewire(CommentList::class, [
        'record' => $post,
    ]);

    expect($defaultComponent->instance()->isReadonly())->toBeFalse();
});

test('readonly comment list properly passes readonly state to child comments', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment',
    ]);

    $component = livewire(CommentList::class, [
        'record' => $post,
        'readonly' => true,
    ]);

    // Check that the view contains readonly true for child components
    $component->assertSee('Test comment');

    // Verify the component correctly identifies as readonly
    expect($component->instance()->isReadonly())->toBeTrue();
});

test('non-readonly comment list allows interaction', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment',
    ]);

    $component = livewire(CommentList::class, [
        'record' => $post,
        'readonly' => false,
    ]);

    // Check that the view contains the comment
    $component->assertSee('Test comment');

    // Verify the component correctly identifies as not readonly
    expect($component->instance()->isReadonly())->toBeFalse();
});
