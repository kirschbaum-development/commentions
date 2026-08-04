<?php

namespace Tests\Livewire\Concerns;

use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Livewire\Concerns\HasPagination;
use Kirschbaum\Commentions\RenderableComment;
use Mockery;
use Tests\Models\Post;
use Tests\Models\User;

it('returns true when using getComments vs comments in hasMore', function () {
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

    $items = collect([
        new RenderableComment(id: 1, authorName: 'System', body: 'System notice 1'),
        new RenderableComment(id: 2, authorName: 'Bot', body: 'Automated message'),
        new RenderableComment(id: 3, authorName: 'Bot', body: 'Automated message 2'),
        new RenderableComment(id: 4, authorName: 'Bot', body: 'Automated message 2'),
        new RenderableComment(id: 5, authorName: 'Bot', body: 'Automated message 4'),
    ]);

    $record = Mockery::mock();
    $record->shouldReceive('comments')->never()->andReturn($comment);
    $record->shouldReceive('getComments')->once()->andReturn($items->merge([$comment]));

    $component = new class
    {
        use HasPagination;

        public bool $paginate = true;

        public $record;
    };

    $component->record = $record;

    expect($component->hasMore())->toBeTrue();
});
