<?php

namespace Kirschbaum\Commentions\Actions;

use Illuminate\Http\UploadedFile;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\CommentAttachment;

class StoreCommentAttachments
{
    /**
     * Persist uploaded files as attachments for the given comment.
     *
     * @param  array<mixed>  $files
     * @return array<CommentAttachment>
     */
    public function __invoke(Comment $comment, array $files): array
    {
        $disk = (string) config('commentions.attachments.disk', 'public');
        $directory = (string) config('commentions.attachments.directory', 'commentions-attachments');

        $attachments = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store($directory, $disk);

            if (! is_string($path)) {
                continue;
            }

            $attachments[] = $comment->attachments()->create([
                'disk' => $disk,
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return $attachments;
    }

    /**
     * @return array<CommentAttachment>
     */
    public static function run(...$args): array
    {
        return (new self())(...$args);
    }
}
