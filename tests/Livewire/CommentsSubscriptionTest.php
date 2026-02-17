<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Kirschbaum\Commentions\CommentSubscription;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Comments;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

test('sidebar visibility can be disabled via parameter', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
        // Livewire binds mount* params by name; HasSidebar expects `enableSidebar`.
        'enableSidebar' => false,
    ])->assertSet('resolvedSidebarEnabled', false);
});

test('canSubscribe reflects auth state', function () {
    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
    ])->assertSet('canSubscribe', false);

    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    livewire(Comments::class, [
        'record' => $post,
    ])->assertSet('canSubscribe', true);
});

test('isSubscribed and subscribers computed properties reflect DB state', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    /** @var Post $post */
    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
    ])->assertSet('isSubscribed', false)
        ->assertSet('subscribers', fn ($subscribers) => $subscribers instanceof \Illuminate\Support\Collection && $subscribers->isEmpty());

    $post->subscribe($user);

    livewire(Comments::class, [
        'record' => $post,
    ])->assertSet('isSubscribed', true)
        ->assertSet('subscribers', fn ($subscribers) => $subscribers->contains('id', $user->id));
});

test('toggleSubscription subscribes and unsubscribes the current user', function () {
    Event::fake();

    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    /** @var Post $post */
    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
    ])->call('toggleSubscription')
        ->assertSet('isSubscribed', true);

    expect(CommentSubscription::query()->where([
        'subscribable_type' => $post->getMorphClass(),
        'subscribable_id' => $post->getKey(),
        'subscriber_type' => $user->getMorphClass(),
        'subscriber_id' => $user->getKey(),
    ])->exists())->toBeTrue();

    livewire(Comments::class, [
        'record' => $post,
    ])->call('toggleSubscription')
        ->assertSet('isSubscribed', false);

    expect(CommentSubscription::query()->where([
        'subscribable_type' => $post->getMorphClass(),
        'subscribable_id' => $post->getKey(),
        'subscriber_type' => $user->getMorphClass(),
        'subscriber_id' => $user->getKey(),
    ])->exists())->toBeFalse();
});

test('showSubscribers defaults to config when not provided', function () {
    config(['commentions.subscriptions.show_subscribers' => false]);

    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
    ])->assertSet('showSubscribers', false);
});
