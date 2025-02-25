<?php

use DateTime;
use Tests\Models\Post;
use Tests\Models\User;
use Tests\Models\Activity;
use Illuminate\Support\Carbon;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;
use Kirschbaum\Commentions\RenderableComment;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Livewire\Comment as CommentComponent;

test('can render a comment', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'body' => 'Test comment body',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->assertSee('Test comment body')
        ->assertSee($comment->author->name)
        ->assertSee($comment->getCreatedAt()->diffForHumans());
});

test('can render a custom renderable comment', function () {
    $comment = new RenderableComment(
        id: 1,
        authorName: 'System',
        body: 'System notification'
    );

    livewire(CommentComponent::class, [
        'comment' => $comment,
    ])
        ->assertSee('System notification')
        ->assertSee('System')
        ->assertDontSeeHtml('wire:click="edit"')  // Should not show edit button
        ->assertDontSeeHtml('wire:click="delete"'); // Should not show delete button
});
