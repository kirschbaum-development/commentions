<?php

use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Comment as CommentComponent;
use Kirschbaum\Commentions\RenderableComment;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
    Config::flushCommentActions();
});

afterEach(function () {
    Config::flushCommentActions();
});

test('custom comment actions registered via Config are rendered', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    Config::registerCommentActions(fn (CommentModel $comment) => Action::make('logs')
        ->label('Activity Logs')
        ->icon('heroicon-s-clock'));

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertActionVisible('logs')
        ->assertSee('Activity Logs');
});

test('custom comment actions can be called', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    Config::registerCommentActions(fn (CommentModel $comment) => Action::make('ping')
        ->action(fn () => $comment->update(['body' => 'pinged'])));

    livewire(CommentComponent::class, ['comment' => $comment])
        ->callAction('ping');

    expect($comment->fresh()->body)->toBe('pinged');
});

test('custom comment action callbacks receive the comment instance', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    $received = null;

    Config::registerCommentActions(function (CommentModel $resolved) use (&$received) {
        $received = $resolved;

        return Action::make('noop');
    });

    livewire(CommentComponent::class, ['comment' => $comment]);

    expect($received)->not->toBeNull()
        ->and($received->is($comment))->toBeTrue();
});

test('custom comment actions are not rendered for non-comment renderables', function () {
    $user = User::factory()->create();
    actingAs($user);

    Config::registerCommentActions(fn (CommentModel $comment) => Action::make('logs')->label('Activity Logs'));

    livewire(CommentComponent::class, [
        'comment' => new RenderableComment(
            id: 1,
            authorName: 'System',
            body: 'System notification',
        ),
    ])->assertActionDoesNotExist('logs');
});
