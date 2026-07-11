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

    public function commentsQuery(): MorphMany
    {
        $query = $this->comments()
            ->latest()
            ->with(['author', 'reactions.reactor']);

        if (config('commentions.threading.enabled', false)) {
            $query
                ->whereNull('parent_id')
                ->with($this->threadedRepliesEagerLoad());
        }

        return $query;
    }

    public function comment(string $body, ?Commenter $author): Comment
    {
        return SaveComment::run($this, $author, $body);
    }

    public function getComments(?int $limit = null): Collection
    {
        if ($limit) {
            return $this->commentsQuery()->limit($limit)->get();
        }

        return $this->commentsQuery()->get();
    }

    public function subscribe(Commenter $subscriber): void
    {
        CommentSubscription::query()->firstOrCreate([
            'subscribable_type' => $this->getMorphClass(),
            'subscribable_id' => $this->getKey(),
            'subscriber_type' => $subscriber->getMorphClass(),
            'subscriber_id' => $subscriber->getKey(),
        ]);
    }

    public function unsubscribe(Commenter $subscriber): void
    {
        CommentSubscription::query()->where([
            'subscribable_type' => $this->getMorphClass(),
            'subscribable_id' => $this->getKey(),
            'subscriber_type' => $subscriber->getMorphClass(),
            'subscriber_id' => $subscriber->getKey(),
        ])->delete();
    }

    public function isSubscribed(Commenter $subscriber): bool
    {
        return CommentSubscription::query()->where([
            'subscribable_type' => $this->getMorphClass(),
            'subscribable_id' => $this->getKey(),
            'subscriber_type' => $subscriber->getMorphClass(),
            'subscriber_id' => $subscriber->getKey(),
        ])->exists();
    }

    /**
     * @return Collection<int, Commenter>
     */
    public function getSubscribers(): Collection
    {
        $commenterModel = Config::getCommenterModel();

        return CommentSubscription::query()
            ->where('subscribable_type', $this->getMorphClass())
            ->where('subscribable_id', $this->getKey())
            ->get()
            ->map(function (CommentSubscription $subscription) use ($commenterModel) {
                return $commenterModel::whereKey($subscription->subscriber_id)->first();
            })
            ->filter();
    }

    /**
     * Eager-load paths for nested replies down to the configured max depth.
     *
     * @return array<int, string>
     */
    protected function threadedRepliesEagerLoad(): array
    {
        $maxDepth = max(0, (int) config('commentions.threading.max_depth', 3));

        $paths = [];
        $prefix = 'replies';

        for ($level = 1; $level <= $maxDepth; $level++) {
            $paths[] = $prefix;
            $paths[] = $prefix . '.author';
            $paths[] = $prefix . '.reactions.reactor';
            $prefix .= '.replies';
        }

        return $paths;
    }
}
