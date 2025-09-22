<?php

use Illuminate\Support\Facades\Event;
use Kirschbaum\Commentions\Events\CommentWasCreatedEvent;
use Kirschbaum\Commentions\Livewire\Comments;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

test('readonly comments component prevents creating new comments', function () {
    Event::fake();

    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
        'readonly' => true,
    ])
        ->set('commentBody', 'This should not be saved')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('commentBody', 'This should not be saved'); // Body should not be cleared since save was prevented

    assertDatabaseMissing('comments', [
        'body' => 'This should not be saved',
        'commentable_id' => $post->id,
        'commentable_type' => Post::class,
        'author_id' => $user->id,
    ]);

    Event::assertNotDispatched(CommentWasCreatedEvent::class);
});

test('readonly comments component isReadonly method returns correct value', function () {
    $post = Post::factory()->create();

    // Test readonly true
    $readonlyComponent = livewire(Comments::class, [
        'record' => $post,
        'readonly' => true,
    ]);

    expect($readonlyComponent->instance()->isReadonly())->toBeTrue();

    // Test readonly false
    $editableComponent = livewire(Comments::class, [
        'record' => $post,
        'readonly' => false,
    ]);

    expect($editableComponent->instance()->isReadonly())->toBeFalse();

    // Test default value
    $defaultComponent = livewire(Comments::class, [
        'record' => $post,
    ]);

    expect($defaultComponent->instance()->isReadonly())->toBeFalse();
});

test('non-readonly comments component allows creating comments', function () {
    Event::fake();

    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
        'readonly' => false,
    ])
        ->set('commentBody', 'This should be saved')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('commentBody', ''); // Body should be cleared after successful save

    assertDatabaseHas('comments', [
        'body' => 'This should be saved',
        'commentable_id' => $post->id,
        'commentable_type' => Post::class,
        'author_id' => $user->id,
    ]);

    Event::assertDispatched(CommentWasCreatedEvent::class);
});

test('readonly comments component works with Filament 4 view field integration', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    // Simulate how it would be used in a Filament 4 form ViewField
    $component = livewire(Comments::class, [
        'record' => $post,
        'readonly' => true,
    ]);

    expect($component->instance()->isReadonly())->toBeTrue();

    // Should not allow saving even when trying to bypass readonly
    $component
        ->set('commentBody', 'Attempt to bypass readonly')
        ->call('save');

    expect($component->get('commentBody'))->toBe('Attempt to bypass readonly');

    assertDatabaseMissing('comments', [
        'body' => 'Attempt to bypass readonly',
    ]);
});
