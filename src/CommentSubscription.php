<?php

namespace Kirschbaum\Commentions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Model $subscribable
 * @property-read Model $subscriber
 */
class CommentSubscription extends Model
{
    protected $fillable = [
        'subscribable_id',
        'subscribable_type',
        'subscriber_id',
        'subscriber_type',
    ];

    public function getTable()
    {
        return Config::getCommentSubscriptionTable();
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }
}
