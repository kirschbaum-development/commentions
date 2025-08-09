<?php

namespace Kirschbaum\Commentions\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;
use Kirschbaum\Commentions\Notifications\UserMentionedInComment;

class SendUserMentionedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserWasMentionedEvent $event): void
    {
        $user = $event->user;

        if (! config('commentions.notifications.mentions.enabled', false)) {
            return;
        }

        $channels = (array) config('commentions.notifications.mentions.channels', []);
        if (empty($channels)) {
            return;
        }

        $notificationClass = (string) config('commentions.notifications.mentions.notification', UserMentionedInComment::class);
        $notification = app($notificationClass, ['comment' => $event->comment, 'channels' => $channels]);

        Notification::send($user, $notification);
    }
}
