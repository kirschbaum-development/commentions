<?php

use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Config;
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
