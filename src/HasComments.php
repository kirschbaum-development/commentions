<?php

namespace Kirschbaum\Commentions;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Contracts\Commenter;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Config::getCommentModel(), 'commentable');
    }

    public function comment(string $body, ?Commenter $author): Comment
    {
        return SaveComment::run($this, $author, $body);
    }

    public function getComments(): Collection
    {
        return $this->comments()
            ->latest()
            ->with(['author', 'reactions.reactor'])
            ->get();
    }
}
