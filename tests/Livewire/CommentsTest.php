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

test('can create a comment', function () {
    Event::fake();

    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
    ])
        ->set('commentBody', 'This is a test comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('commentBody', '')
        ->assertDispatched('comment:saved');

    assertDatabaseHas('comments', [
        'body' => 'This is a test comment',
        'commentable_id' => $post->id,
        'commentable_type' => Post::class,
        'author_id' => $user->id,
    ]);

    Event::assertDispatched(CommentWasCreatedEvent::class, function ($event) use ($post, $user) {
        return $event->comment->body === 'This is a test comment' &&
               $event->comment->commentable_id === $post->id &&
               $event->comment->commentable_type === Post::class &&
               $event->comment->author_id === $user->id;
    });
});

test('comment creation requires body', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
    ])
        ->set('commentBody', '')
        ->call('save')
        ->assertHasErrors(['commentBody' => 'required']);

    assertDatabaseMissing('comments', [
        'commentable_id' => $post->id,
        'commentable_type' => Post::class,
    ]);
});

test('guests cannot create comments', function () {
    Event::fake();

    $post = Post::factory()->create();

    expect(function () use ($post) {
        livewire(Comments::class, [
            'record' => $post,
        ])
            ->set('commentBody', 'This is a test comment')
            ->call('save');
    })->toThrow(TypeError::class);

    assertDatabaseMissing('comments', [
        'body' => 'This is a test comment',
        'commentable_id' => $post->id,
        'commentable_type' => Post::class,
    ]);

    Event::assertNotDispatched(CommentWasCreatedEvent::class);
});
