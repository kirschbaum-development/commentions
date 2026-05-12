<?php

use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Livewire\CommentList;
use Kirschbaum\Commentions\RenderableComment;
use Mockery;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Livewire\livewire;

test('CommentList calls getComments when not paginating', function () {
    /** @var Post|Mockery\MockInterface $post */
    $post = Mockery::mock(Post::class)->makePartial();

    $post->shouldReceive('getComments')
        ->once()
        ->andReturn(collect());

    $component = livewire(CommentList::class, [
        'record' => $post,
        'paginate' => false,
    ]);

    $component->get('comments');
});

test('CommentList calls getComments when paginating', function () {
    /** @var Post|Mockery\MockInterface $post */
    $post = Mockery::mock(Post::class)->makePartial();

    $post->shouldReceive('getComments')
        ->once()
        ->andReturn(collect());

    $component = livewire(CommentList::class, [
        'record' => $post,
        'paginate' => true,
        'perPage' => 5,
    ]);

    $component->get('comments');
});

test('CommentList can render non-Comment renderable items', function () {
    /** @var Post|Mockery\MockInterface $post */
    $post = Mockery::mock(Post::class)->makePartial();

    $items = collect([
        new RenderableComment(id: 1, authorName: 'System', body: 'System notice 1'),
        new RenderableComment(id: 2, authorName: 'Bot', body: 'Automated message'),
    ]);

    $post->shouldReceive('getComments')
        ->once()
        ->andReturn($items);

    livewire(CommentList::class, [
        'record' => $post,
        'paginate' => false,
    ])
        ->assertSee('System')
        ->assertSee('System notice 1')
        ->assertSee('Bot')
        ->assertSee('Automated message');
});

test('CommentList renders duplicate-content comments without key collision', function () {
    /** @var User $user */
    $user = User::factory()->create();
    /** @var Post $realPost */
    $realPost = Post::factory()->create();

    $first = CommentModel::factory()->author($user)->commentable($realPost)->create([
        'body' => 'identical body',
    ]);
    $second = CommentModel::factory()->author($user)->commentable($realPost)->create([
        'body' => 'identical body',
    ]);

    livewire(CommentList::class, [
        'record' => $realPost,
        'paginate' => false,
    ])
        ->assertSeeHtml('wire:key="' . $first->id . '"')
        ->assertSeeHtml('wire:key="' . $second->id . '"');
});

test('CommentList can render both Comment and RenderableComment items', function () {
    /** @var User $user */
    $user = User::factory()->create();
    /** @var Post $realPost */
    $realPost = Post::factory()->create();

    /** @var CommentModel $comment */
    $comment = CommentModel::factory()
        ->author($user)
        ->commentable($realPost)
        ->create([
            'body' => 'Real comment body',
        ]);

    $renderable = new RenderableComment(id: 99, authorName: 'System', body: 'System message');

    $items = collect([$comment, $renderable]);

    /** @var Post|Mockery\MockInterface $post */
    $post = Mockery::mock(Post::class)->makePartial();
    $post->shouldReceive('getComments')
        ->once()
        ->andReturn($items);

    livewire(CommentList::class, [
        'record' => $post,
        'paginate' => false,
    ])
        // From Eloquent Comment
        ->assertSee('Real comment body')
        ->assertSee($user->name)
        // From RenderableComment
        ->assertSee('System')
        ->assertSee('System message');
});
