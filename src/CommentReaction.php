<?php

namespace Kirschbaum\Commentions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kirschbaum\Commentions\Contracts\Commenter;

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
        return $this->belongsTo(Config::getCommentModel());
    }

    /** @return MorphTo<Commenter> */
    public function reactor(): MorphTo
    {
        return $this->morphTo();
    }
}
