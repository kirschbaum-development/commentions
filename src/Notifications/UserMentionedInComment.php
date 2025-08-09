<?php

namespace Kirschbaum\Commentions\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Manager;

class UserMentionedInComment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Comment $comment,
        protected array $channels
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = Config::resolveCommentUrl($this->comment) ?? url('/');

        return (new MailMessage())
            ->subject((string) config('commentions.notifications.mentions.mail.subject', 'You were mentioned in a comment'))
            ->greeting('Hi ' . Manager::getName($notifiable))
            ->line('You were mentioned in a comment by ' . $this->comment->getAuthorName() . '.')
            ->line(strip_tags($this->comment->getBodyMarkdown()))
            ->action('View comment', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->getId(),
            'comment_body' => $this->comment->getBody(),
            'author_name' => $this->comment->getAuthorName(),
            'url' => Config::resolveCommentUrl($this->comment),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
