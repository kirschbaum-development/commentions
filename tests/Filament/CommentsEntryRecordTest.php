<?php

use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Kirschbaum\Commentions\Livewire\Concerns\InteractsWithCommentSchemas;
use Kirschbaum\Commentions\Livewire\Concerns\InteractsWithCommentSchemasBridge;
use Livewire\Component;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

/**
 * Builds a `CommentsEntry` inside a schema whose container record is `$pageRecord`,
 * with the entry's own record overridden by `$override`.
 */
function commentsEntryWithRecordOverride(Post $pageRecord, mixed $override): CommentsEntry
{
    /** @var CommentsEntry $entry */
    $entry = Schema::make()
        ->record($pageRecord)
        ->components([
            CommentsEntry::make('comments')->record($override),
        ])
        ->getComponents()[0];

    return $entry;
}

/**
 * Minimal Livewire host rendering a `CommentsEntry` whose record is overridden
 * by a closure that type-hints the record, mirroring the reported usage.
 */
class CommentsEntryRecordOverrideHarness extends Component implements HasSchemas
{
    use InteractsWithCommentSchemas;
    use InteractsWithCommentSchemasBridge;

    public Post $record;

    public Post $target;

    public function commentsInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([
                CommentsEntry::make('comments')
                    ->record(fn (Post $record) => $this->target),
            ]);
    }

    public function render()
    {
        return <<<'BLADE'
        <div>{{ $this->getSchema('commentsInfolist') }}</div>
        BLADE;
    }
}

test('CommentsEntry record accepts a closure that type-hints the record', function () {
    $page = Post::factory()->create();
    $target = Post::factory()->create();

    $entry = commentsEntryWithRecordOverride($page, fn (Post $record) => $target);

    expect($entry->getRecord()->getKey())->toBe($target->getKey());
});

test('CommentsEntry record accepts a closure with a $record parameter', function () {
    $page = Post::factory()->create();
    $target = Post::factory()->create();

    $entry = commentsEntryWithRecordOverride($page, fn ($record) => $target);

    expect($entry->getRecord()->getKey())->toBe($target->getKey());
});

test('CommentsEntry record injects the container record into the closure', function () {
    $page = Post::factory()->create();

    $entry = commentsEntryWithRecordOverride($page, fn (Post $record) => $record);

    expect($entry->getRecord()->getKey())->toBe($page->getKey());
});

test('CommentsEntry record accepts a closure without parameters', function () {
    $page = Post::factory()->create();
    $target = Post::factory()->create();

    $entry = commentsEntryWithRecordOverride($page, fn () => $target);

    expect($entry->getRecord()->getKey())->toBe($target->getKey());
});

test('CommentsEntry record accepts a model instance', function () {
    $page = Post::factory()->create();
    $target = Post::factory()->create();

    $entry = commentsEntryWithRecordOverride($page, $target);

    expect($entry->getRecord()->getKey())->toBe($target->getKey());
});

test('CommentsEntry falls back to the container record when not overridden', function () {
    $page = Post::factory()->create();

    /** @var CommentsEntry $entry */
    $entry = Schema::make()
        ->record($page)
        ->components([CommentsEntry::make('comments')])
        ->getComponents()[0];

    expect($entry->getRecord()->getKey())->toBe($page->getKey());
});

test('CommentsEntry renders comments for the overridden record', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $page = Post::factory()->create();
    $target = Post::factory()->create();

    $page->comment('Comment on the page record', $user);
    $target->comment('Comment on the overridden record', $user);

    livewire(CommentsEntryRecordOverrideHarness::class, [
        'record' => $page,
        'target' => $target,
    ])
        ->assertSee('Comment on the overridden record')
        ->assertDontSee('Comment on the page record');
});
