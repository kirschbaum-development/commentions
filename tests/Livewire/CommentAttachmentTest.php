<?php

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
        ->set('attachments', [UploadedFile::fake()->create('report.pdf', 120, 'application/pdf')])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('attachments', []);

    $comment = Comment::query()->latest('id')->first();

    expect($comment->attachments)->toHaveCount(1);
    expect($comment->attachments->first()->filename)->toBe('report.pdf');

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
