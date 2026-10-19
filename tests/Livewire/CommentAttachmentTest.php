<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\CommentAttachment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Kirschbaum\Commentions\Livewire\Comments;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeTime;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
    Storage::fake('public');
});

test('a file can be attached to a comment', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'attachmentsEnabled' => true])
        ->set('commentBody', 'See the attached file')
        ->set('attachments', [UploadedFile::fake()->create('report.txt', 120, 'text/plain')])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('attachments', []);

    $comment = Comment::query()->latest('id')->first();

    expect($comment->attachments)->toHaveCount(1);
    expect($comment->attachments->first()->filename)->toBe('report.txt');

    Storage::disk('public')->assertExists($comment->attachments->first()->path);
});

test('the attach control is hidden when attachments are disabled', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post])
        ->assertDontSeeHtml('wire:model="attachments"');
});

test('the attach control renders when attachments are enabled', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'attachmentsEnabled' => true])
        ->assertSeeHtml('wire:model="attachments"');
});

test('the attach control is only shown once the editor is focused', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'attachmentsEnabled' => true])
        ->assertSeeHtml('x-show="wasFocused"');
});

test('deleting a comment removes its attachments and files', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'attachmentsEnabled' => true])
        ->set('commentBody', 'With a file')
        ->set('attachments', [UploadedFile::fake()->create('notes.txt', 10)])
        ->call('save');

    $comment = Comment::query()->latest('id')->first();
    $attachment = $comment->attachments->first();

    expect($attachment)->not->toBeNull();
    Storage::disk('public')->assertExists($attachment->path);

    $comment->delete();

    test()->assertDatabaseMissing('comment_attachments', ['id' => $attachment->id]);
    Storage::disk('public')->assertMissing($attachment->path);
});

test('oversized attachments are rejected and the comment is not created', function () {
    config(['commentions.attachments.max_size' => 100]);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'attachmentsEnabled' => true])
        ->set('commentBody', 'Too big')
        ->set('attachments', [UploadedFile::fake()->create('huge.pdf', 500)])
        ->call('save')
        ->assertHasErrors('attachments.0');

    test()->assertDatabaseCount('comments', 0);
});

test('attachments with a disallowed mime type are rejected', function () {
    config(['commentions.attachments.accepted_mime_types' => ['application/pdf']]);

    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    livewire(Comments::class, ['record' => $post, 'attachmentsEnabled' => true])
        ->set('commentBody', 'Sneaky')
        ->set('attachments', [UploadedFile::fake()->create('xss.svg', 10, 'image/svg+xml')])
        ->call('save')
        ->assertHasErrors('attachments.0');

    test()->assertDatabaseCount('comments', 0);
});

test('a pending attachment can be removed before saving', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::factory()->create();

    $component = livewire(Comments::class, ['record' => $post, 'attachmentsEnabled' => true])
        ->set('attachments', [
            UploadedFile::fake()->create('one.pdf', 10),
            UploadedFile::fake()->create('two.pdf', 10),
        ]);

    expect($component->get('attachments'))->toHaveCount(2);

    $component->call('removeAttachment', 0);

    expect($component->get('attachments'))->toHaveCount(1);
});

test('CommentsEntry enableAttachments toggles attachment support', function () {
    expect(CommentsEntry::make('comments')->attachmentsAreEnabled())->toBeFalse()
        ->and(CommentsEntry::make('comments')->enableAttachments()->attachmentsAreEnabled())->toBeTrue();
});

test('CommentAttachment identifies image attachments', function () {
    expect((new CommentAttachment(['mime_type' => 'image/png']))->isImage())->toBeTrue()
        ->and((new CommentAttachment(['mime_type' => 'application/pdf']))->isImage())->toBeFalse();
});

function storedAttachmentOn(string $disk): CommentAttachment
{
    Storage::disk($disk)->put('commentions-attachments/report.txt', 'quarterly numbers');

    return Comment::factory()
        ->author(User::factory()->create())
        ->commentable(Post::factory()->create())
        ->create()
        ->attachments()
        ->create([
            'disk' => $disk,
            'path' => 'commentions-attachments/report.txt',
            'filename' => 'report.txt',
            'mime_type' => 'text/plain',
            'size' => 17,
        ]);
}

// Points the private `local` disk (served through Laravel's signed
// storage route) at an isolated directory, without faking it, so the
// real route can be hit.
function usePrivateLocalDisk(array $config = []): void
{
    $root = storage_path('framework/testing/disks/commentions-private');

    (new Filesystem())->cleanDirectory($root);

    config(['filesystems.disks.local' => array_merge(config('filesystems.disks.local'), ['root' => $root], $config)]);

    Storage::forgetDisk('local');
}

test('attachments on a private disk get a signed url that can be opened', function () {
    usePrivateLocalDisk();

    $attachment = storedAttachmentOn('local');

    get($attachment->getUrl())->assertOk();
});

test('signed attachment urls expire after the configured number of minutes', function () {
    config(['commentions.attachments.url_expiration' => 5]);
    usePrivateLocalDisk();
    freezeTime();

    parse_str((string) parse_url(storedAttachmentOn('local')->getUrl(), PHP_URL_QUERY), $query);

    expect((int) $query['expires'])->toBe(now()->addMinutes(5)->getTimestamp());
});

test('attachments on a public disk keep a plain url', function () {
    $attachment = storedAttachmentOn('public');

    expect($attachment->getUrl())->toBe(Storage::disk('public')->url($attachment->path));
});

test('attachments on a disk that cannot sign urls fall back to a plain url', function () {
    usePrivateLocalDisk(['serve' => false]);

    $attachment = storedAttachmentOn('local');

    expect($attachment->getUrl())->toBe(Storage::disk('local')->url($attachment->path));
});
