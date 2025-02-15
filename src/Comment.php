<?php

namespace Kirschbaum\FilamentComments;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kirschbaum\FilamentComments\Contracts\Commenter;
use Kirschbaum\FilamentComments\Actions\ParseComment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kirschbaum\FilamentComments\Contracts\Commentable;
use Kirschbaum\FilamentComments\Actions\HtmlToMarkdown;
use Kirschbaum\FilamentComments\Database\Factories\CommentFactory;

/**
 * @property int $id
 * @property string $body
 * @property string $body_markdown
 * @property string $body_parsed
 * @property User $author
 * @property Commentable $commentable
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'author_type',
        'author_id',
    ];

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function bodyParsed(): Attribute
    {
        return Attribute::make(
            get: fn () => ParseComment::run($this->body),
        );
    }

    public function bodyMarkdown(): Attribute
    {
        return Attribute::make(
            get: fn () => HtmlToMarkdown::run($this->body),
        );
    }

    public function getBodyMarkdown(Closure $mentionedCallback = null): string
    {
        return HtmlToMarkdown::run(
            html: $this->body,
            mentionedCallback: $mentionedCallback,
        );
    }
    
    public function isAuthor(Commenter $author)
    {
        return $this->author_id === $author->getKey();
    }

    protected static function newFactory()
    {
        return CommentFactory::new();
    }
}
