<?php

namespace Kirschbaum\Commentions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read Comment $comment
 * @property string $disk
 * @property string $path
 * @property string $filename
 * @property string|null $mime_type
 * @property int|null $size
 */
class CommentAttachment extends Model
{
    protected $fillable = [
        'comment_id',
        'disk',
        'path',
        'filename',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function getTable()
    {
        return Config::getCommentAttachmentTable();
    }

    /** @return BelongsTo<Comment> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Config::getCommentModel());
    }

    public function getUrl(): string
    {
        $disk = Storage::disk($this->disk);

        // Files on a private disk are only reachable through a signed URL.
        // Missing visibility means private, matching Laravel's own file serving.
        $isPublic = config("filesystems.disks.{$this->disk}.visibility") === 'public';

        if (! $isPublic && $disk->providesTemporaryUrls()) {
            return $disk->temporaryUrl(
                $this->path,
                now()->addMinutes((int) config('commentions.attachments.url_expiration', 30))
            );
        }

        return $disk->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    protected static function booted(): void
    {
        static::deleted(function (CommentAttachment $attachment): void {
            Storage::disk($attachment->disk)->delete($attachment->path);
        });
    }
}
