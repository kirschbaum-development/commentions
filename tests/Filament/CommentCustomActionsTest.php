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

test('hidden custom comment actions are not rendered in the html', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    Config::registerCommentActions(fn (CommentModel $comment) => Action::make('deleteFiles')
        ->label('Delete attached files')
        ->visible(false));

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertActionHidden('deleteFiles')
        ->assertDontSee('Delete attached files');
});

test('custom comment action visibility is evaluated against the comment', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    Config::registerCommentActions(fn (CommentModel $comment) => Action::make('deleteFiles')
        ->label('Delete attached files')
        ->visible(fn (): bool => $comment->attachments()->exists()));

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertActionHidden('deleteFiles')
        ->assertDontSee('Delete attached files');
});

test('visible custom comment actions are rendered in the html', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    Config::registerCommentActions(fn (CommentModel $comment) => Action::make('deleteFiles')
        ->label('Delete attached files')
        ->visible(true));

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertActionVisible('deleteFiles')
        ->assertSee('Delete attached files');
});

test('custom comment actions default to extra small size', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    Config::registerCommentActions(fn (CommentModel $comment) => Action::make('logs'));

    $component = livewire(CommentComponent::class, ['comment' => $comment]);

    expect($component->instance()->getAction('logs')->getSize())->toBe('xs');
});

test('custom comment actions can override the default size', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create();

    Config::registerCommentActions(fn (CommentModel $comment) => Action::make('logs')->size('sm'));

    $component = livewire(CommentComponent::class, ['comment' => $comment]);

    expect($component->instance()->getAction('logs')->getSize())->toBe('sm');
});
