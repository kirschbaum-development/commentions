<?php

namespace Kirschbaum\Commentions;

use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Contracts\Commenter;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function comment(string $body, ?Commenter $author): Comment
    {
        return SaveComment::run($this, $author, $body);
    }

    public function getComments(): Collection
    {
        return $this->comments()->latest()->with('author')->get();
    }
}
