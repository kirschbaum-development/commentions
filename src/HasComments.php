<?php

namespace Kirschbaum\FilamentComments;

use Kirschbaum\FilamentComments\Actions\SaveComment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function comment(string $body, ?CommentAuthor $author): Comment
    {
        return SaveComment::run($this, $author, $body);
    }
}
