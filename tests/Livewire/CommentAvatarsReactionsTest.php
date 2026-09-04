<?php

use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;
use Kirschbaum\Commentions\Filament\Actions\CommentsTableAction;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Kirschbaum\Commentions\Livewire\Comment as CommentComponent;
use Kirschbaum\Commentions\Livewire\Comments;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

test('CommentsEntry record overrides the container record', function () {
    $pagePost = Post::factory()->create();
    $pivotPost = Post::factory()->create();

    $entry = CommentsEntry::make('comments')->record($pivotPost);

    // Explicit record wins without needing a container.
    expect($entry->getRecord(false))->toBe($pivotPost);

    // Also resolves via getRecord() when a container exists is covered by
    // the infolist harness in CommentsEntryTest; here we assert the override
    // is stored and returned.
    expect($entry->getRecord(false)->is($pivotPost))->toBeTrue()
        ->and($entry->getRecord(false)->is($pagePost))->toBeFalse();
});

test('CommentsEntry record accepts a closure', function () {
    $post = Post::factory()->create();

    $entry = CommentsEntry::make('comments')->record(fn () => $post);

    expect($entry->getRecord(false)->is($post))->toBeTrue();
});

test('avatars render by default and can be disabled per component', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Kirschbaum\Commentions\Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Avatar test',
    ]);

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertSee('User Avatar', false);

    livewire(CommentComponent::class, ['comment' => $comment, 'avatarsEnabled' => false])
        ->assertDontSee('User Avatar', false);

    livewire(Comments::class, ['record' => $post, 'avatarsEnabled' => false])
        ->assertDontSee('User Avatar', false);
});

test('avatars can be disabled globally via config', function () {
    config()->set('commentions.avatars.enabled', false);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Kirschbaum\Commentions\Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Avatar config test',
    ]);

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertDontSee('User Avatar', false);

    expect(Config::avatarsAreEnabled())->toBeFalse();
});

test('CommentsEntry, CommentsAction and CommentsTableAction support avatar configuration', function () {
    expect(CommentsEntry::make('comments')->avatarsAreEnabled())->toBeTrue()
        ->and(CommentsEntry::make('comments')->disableAvatars()->avatarsAreEnabled())->toBeFalse()
        ->and(CommentsEntry::make('comments')->enableAvatars(false)->avatarsAreEnabled())->toBeFalse()
        ->and(CommentsAction::make()->disableAvatars()->avatarsAreEnabled())->toBeFalse()
        ->and(CommentsTableAction::make()->disableAvatars()->avatarsAreEnabled())->toBeFalse();

    config()->set('commentions.avatars.enabled', false);

    expect(CommentsEntry::make('comments')->avatarsAreEnabled())->toBeFalse()
        ->and(CommentsEntry::make('comments')->enableAvatars()->avatarsAreEnabled())->toBeTrue();
});

test('reactions render by default and can be disabled per component', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Kirschbaum\Commentions\Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Reaction test',
    ]);

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertSee('Add Reaction', false);

    livewire(CommentComponent::class, ['comment' => $comment, 'reactionsEnabled' => false])
        ->assertDontSee('Add Reaction', false);

    livewire(Comments::class, ['record' => $post, 'reactionsEnabled' => false])
        ->assertDontSee('Add Reaction', false);
});

test('reactions can be disabled globally via config', function () {
    config()->set('commentions.reactions.enabled', false);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Kirschbaum\Commentions\Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Reaction config test',
    ]);

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertDontSee('Add Reaction', false);

    expect(Config::reactionsAreEnabled())->toBeFalse();
});

test('CommentsEntry, CommentsAction and CommentsTableAction support reaction configuration', function () {
    expect(CommentsEntry::make('comments')->reactionsAreEnabled())->toBeTrue()
        ->and(CommentsEntry::make('comments')->disableReactions()->reactionsAreEnabled())->toBeFalse()
        ->and(CommentsEntry::make('comments')->enableReactions(false)->reactionsAreEnabled())->toBeFalse()
        ->and(CommentsAction::make()->disableReactions()->reactionsAreEnabled())->toBeFalse()
        ->and(CommentsTableAction::make()->disableReactions()->reactionsAreEnabled())->toBeFalse();

    config()->set('commentions.reactions.enabled', false);

    expect(CommentsEntry::make('comments')->reactionsAreEnabled())->toBeFalse()
        ->and(CommentsEntry::make('comments')->enableReactions()->reactionsAreEnabled())->toBeTrue();
});

test('toggling a reaction is ignored when reactions are disabled', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Kirschbaum\Commentions\Comment::factory()->author($user)->commentable($post)->create();

    livewire(CommentComponent::class, ['comment' => $comment, 'reactionsEnabled' => false])
        ->call('toggleReaction', '👍');

    test()->assertDatabaseMissing('comment_reactions', [
        'comment_id' => $comment->id,
    ]);
});
