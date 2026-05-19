<?php

use Carbon\Carbon;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\RenderableComment;
use Tests\Models\Post;
use Tests\Models\User;

afterEach(function () {
    config()->set('commentions.timezone', null);
    Config::resolveTimezoneUsing(null);
});

test('comment dates are returned unmodified when no timezone is configured', function () {
    config()->set('commentions.timezone', null);

    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'created_at' => Carbon::parse('2026-01-15 12:00:00', 'UTC'),
        'updated_at' => Carbon::parse('2026-01-15 12:00:00', 'UTC'),
    ]);

    expect($comment->getCreatedAt()->format('Y-m-d H:i:s'))
        ->toBe('2026-01-15 12:00:00');
});

test('comment dates are converted when a timezone is configured', function () {
    config()->set('commentions.timezone', 'America/Chicago');

    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'created_at' => Carbon::parse('2026-01-15 12:00:00', 'UTC'),
        'updated_at' => Carbon::parse('2026-01-15 12:00:00', 'UTC'),
    ]);

    expect($comment->getCreatedAt()->format('Y-m-d H:i'))
        ->toBe('2026-01-15 06:00');
});

test('RenderableComment dates respect the timezone config', function () {
    config()->set('commentions.timezone', 'Asia/Tokyo');

    $renderable = new RenderableComment(
        id: 1,
        authorName: 'System',
        body: 'Test',
        createdAt: Carbon::parse('2026-01-15 12:00:00', 'UTC'),
        updatedAt: Carbon::parse('2026-01-15 12:00:00', 'UTC'),
    );

    expect($renderable->getCreatedAt()->format('Y-m-d H:i'))
        ->toBe('2026-01-15 21:00');
});

test('resolveTimezoneUsing closure takes precedence over config', function () {
    config()->set('commentions.timezone', 'UTC');
    Config::resolveTimezoneUsing(fn () => 'Europe/London');

    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'created_at' => Carbon::parse('2026-06-15 12:00:00', 'UTC'),
        'updated_at' => Carbon::parse('2026-06-15 12:00:00', 'UTC'),
    ]);

    // London is BST (UTC+1) in June
    expect($comment->getCreatedAt()->format('Y-m-d H:i'))
        ->toBe('2026-06-15 13:00');
});

test('applyTimezone does not mutate the source instance', function () {
    config()->set('commentions.timezone', 'America/New_York');

    $source = Carbon::parse('2026-01-15 12:00:00', 'UTC');
    $converted = Config::applyTimezone($source);

    expect($source->format('Y-m-d H:i'))->toBe('2026-01-15 12:00')
        ->and($converted->format('Y-m-d H:i'))->toBe('2026-01-15 07:00');
});

test('an empty-string timezone is treated as no timezone', function () {
    Config::resolveTimezoneUsing(fn () => '');

    $source = Carbon::parse('2026-01-15 12:00:00', 'UTC');

    expect(Config::applyTimezone($source)->format('Y-m-d H:i'))
        ->toBe('2026-01-15 12:00');
});

test('resolveTimezoneUsing falls back to the config value when the closure returns null', function () {
    config()->set('commentions.timezone', 'America/Chicago');
    Config::resolveTimezoneUsing(fn () => null);

    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = CommentModel::factory()->author($user)->commentable($post)->create([
        'created_at' => Carbon::parse('2026-01-15 12:00:00', 'UTC'),
        'updated_at' => Carbon::parse('2026-01-15 12:00:00', 'UTC'),
    ]);

    expect($comment->getCreatedAt()->format('Y-m-d H:i'))
        ->toBe('2026-01-15 06:00');
});
