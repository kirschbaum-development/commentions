<?php

namespace Kirschbaum\Commentions;

use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Contracts\Commenter;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Comment $comment
 * @property-read Commenter $reactor
 */
class CommentReaction extends Model
{
    protected $fillable = [
        'comment_id',
        'reactor_id',
        'reactor_type',
        'reaction',
    ];

    /** @return BelongsTo<Comment> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /** @return MorphTo<Commenter> */
    public function reactor(): MorphTo
    {
        return $this->morphTo();
    }
}
