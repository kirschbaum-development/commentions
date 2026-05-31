<?php

use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Kirschbaum\Commentions\Livewire\Comments;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

test('normalizeToolbarButtons wraps a flat list into a single group', function () {
    expect(Config::normalizeToolbarButtons(['bold', 'italic']))
        ->toBe([['bold', 'italic']]);
});

test('normalizeToolbarButtons keeps grouped buttons as-is', function () {
    expect(Config::normalizeToolbarButtons([['bold'], ['link']]))
        ->toBe([['bold'], ['link']]);
});

test('normalizeToolbarButtons returns empty for no buttons', function () {
    expect(Config::normalizeToolbarButtons([]))->toBe([]);
});

test('normalizeToolbarButtons handles mixed array and string input without error', function () {
    expect(Config::normalizeToolbarButtons([['bold'], 'italic']))
        ->toBe([['italic']]);
});

test('normalizeToolbarButtons drops empty groups', function () {
    expect(Config::normalizeToolbarButtons([['bold'], []]))
        ->toBe([['bold']]);
});

test('getToolbarButtons returns empty when the toolbar is disabled', function () {
    config(['commentions.toolbar.enabled' => false]);

    expect(Config::getToolbarButtons())->toBe([]);
});

test('CommentsEntry toolbarButtons normalizes a flat list into a group', function () {
    $entry = CommentsEntry::make('comments')->toolbarButtons(['bold', 'link']);

    expect($entry->getToolbarButtons())->toBe([['bold', 'link']]);
});

test('CommentsEntry returns null toolbar buttons when not configured', function () {
    expect(CommentsEntry::make('comments')->getToolbarButtons())->toBeNull();
});

test('the comment editor renders the configured toolbar buttons', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
        'toolbarButtons' => [['bold', 'italic']],
    ])
        ->assertSeeHtml('data-toolbar-button="bold"')
        ->assertSeeHtml('data-toolbar-button="italic"')
        ->assertDontSeeHtml('data-toolbar-button="link"');
});

test('the comment editor renders no toolbar when buttons are empty', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
        'toolbarButtons' => [],
    ])->assertDontSeeHtml('commentions-toolbar');
});

test('the comment editor falls back to the configured default toolbar', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post])
        ->assertSeeHtml('data-toolbar-button="bold"')
        ->assertSeeHtml('data-toolbar-button="link"');
});

test('the comment editor normalizes a flat toolbar buttons list', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, [
        'record' => $post,
        'toolbarButtons' => ['bold', 'italic'],
    ])
        ->assertSeeHtml('data-toolbar-button="bold"')
        ->assertSeeHtml('data-toolbar-button="italic"');
});
