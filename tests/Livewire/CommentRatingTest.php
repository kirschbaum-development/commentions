<?php

use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Comment;
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

test('a rating can be attached to a comment when ratings are enabled', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'ratingsEnabled' => true])
        ->set('commentBody', 'Great service')
        ->set('rating', 4)
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::query()->latest('id')->first()->rating)->toBe(4);
});

test('the rating is ignored when ratings are disabled', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post])
        ->set('commentBody', 'No rating here')
        ->set('rating', 5)
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::query()->latest('id')->first()->rating)->toBeNull();
});

test('a rating above the configured maximum is rejected', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'ratingsEnabled' => true, 'maxRating' => 5])
        ->set('commentBody', 'Too high')
        ->set('rating', 9)
        ->call('save')
        ->assertHasErrors('rating');

    test()->assertDatabaseCount('comments', 0);
});

test('the rating input renders only when ratings are enabled', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'ratingsEnabled' => true])
        ->assertSeeHtml('commentions-rating-star');

    livewire(Comments::class, ['record' => $post])
        ->assertDontSeeHtml('commentions-rating-star');
});

test('CommentsEntry enableRatings and maxRating configure ratings', function () {
    expect(CommentsEntry::make('comments')->ratingsAreEnabled())->toBeFalse()
        ->and(CommentsEntry::make('comments')->enableRatings()->ratingsAreEnabled())->toBeTrue()
        ->and(CommentsEntry::make('comments')->maxRating(10)->getMaxRating())->toBe(10);
});

test('a comment renders its star rating', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Rated comment',
        'rating' => 3,
    ]);

    livewire(CommentComponent::class, ['comment' => $comment])
        ->assertSeeHtml('title="3/5"');
});

test('a comment renders its rating against a per-component max rating', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Rated comment',
        'rating' => 8,
    ]);

    livewire(CommentComponent::class, ['comment' => $comment, 'maxRating' => 10])
        ->assertSeeHtml('title="8/10"');
});

test('a per-component max rating threads through to the rendered comment', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Rated comment',
        'rating' => 8,
    ]);

    livewire(Comments::class, ['record' => $post, 'ratingsEnabled' => true, 'maxRating' => 10])
        ->assertSeeHtml('title="8/10"');
});

test('a rating below the minimum is rejected', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'ratingsEnabled' => true])
        ->set('commentBody', 'Too low')
        ->set('rating', 0)
        ->call('save')
        ->assertHasErrors('rating');

    test()->assertDatabaseCount('comments', 0);
});

test('ratings can be disabled per component even when enabled globally', function () {
    config()->set('commentions.ratings.enabled', true);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'ratingsEnabled' => false])
        ->set('commentBody', 'No rating')
        ->set('rating', 4)
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::query()->latest('id')->first()->rating)->toBeNull();
});

test('CommentsEntry disableRatings overrides the global config', function () {
    config()->set('commentions.ratings.enabled', true);

    expect(CommentsEntry::make('comments')->ratingsAreEnabled())->toBeTrue()
        ->and(CommentsEntry::make('comments')->disableRatings()->ratingsAreEnabled())->toBeFalse();
});

test('CommentsAction supports rating configuration', function () {
    expect(CommentsAction::make()->ratingsAreEnabled())->toBeFalse()
        ->and(CommentsAction::make()->enableRatings()->ratingsAreEnabled())->toBeTrue()
        ->and(CommentsAction::make()->maxRating(8)->getMaxRating())->toBe(8);
});

test('CommentsTableAction supports rating configuration', function () {
    expect(CommentsTableAction::make()->ratingsAreEnabled())->toBeFalse()
        ->and(CommentsTableAction::make()->enableRatings()->ratingsAreEnabled())->toBeTrue()
        ->and(CommentsTableAction::make()->maxRating(8)->getMaxRating())->toBe(8);
});

test('a comment rating can be edited when ratings are enabled', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Rated comment',
        'rating' => 2,
    ]);

    livewire(CommentComponent::class, ['comment' => $comment, 'ratingsEnabled' => true])
        ->call('edit')
        ->assertSet('rating', 2)
        ->set('rating', 5)
        ->call('updateComment')
        ->assertHasNoErrors();

    expect($comment->refresh()->rating)->toBe(5);
});

test('editing a comment leaves the rating untouched when ratings are disabled', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();
    $comment = Comment::factory()->author($user)->commentable($post)->create([
        'body' => 'Rated comment',
        'rating' => 2,
    ]);

    livewire(CommentComponent::class, ['comment' => $comment])
        ->call('edit')
        ->set('commentBody', 'Edited body')
        ->set('rating', 5)
        ->call('updateComment')
        ->assertHasNoErrors();

    expect($comment->refresh())
        ->rating->toBe(2)
        ->body->toBe('Edited body');
});
