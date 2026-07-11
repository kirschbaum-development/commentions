<?php

use App\Models\User;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Listeners\SendUserMentionedNotification;
use Kirschbaum\Commentions\Notifications\UserMentionedInComment;
use Kirschbaum\Commentions\Policies\CommentPolicy;

return [
    /*
    |--------------------------------------------------------------------------
    | Table name configurations
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'comments' => 'comments',
        'comment_reactions' => 'comment_reactions',
        'comment_subscriptions' => 'comment_subscriptions',
        'comment_attachments' => 'comment_attachments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commenter model configuration
    |--------------------------------------------------------------------------
    */
    'commenter' => [
        'model' => User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Comment model configuration
    |--------------------------------------------------------------------------
    */
    'comment' => [
        'model' => Comment::class,
        'policy' => CommentPolicy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reactions
    |--------------------------------------------------------------------------
    */
    'reactions' => [
        'allowed' => ['👍', '❤️', '😂', '😮', '😢', '🤔'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachments
    |--------------------------------------------------------------------------
    |
    | File attachments on comments. Disabled by default; enable globally here
    | or per component with CommentsEntry::make()->enableAttachments(). Files
    | are stored on the configured filesystem disk.
    |
    */
    'attachments' => [
        'enabled' => env('COMMENTIONS_ATTACHMENTS_ENABLED', false),

        'disk' => env('COMMENTIONS_ATTACHMENTS_DISK', 'public'),

        'directory' => env('COMMENTIONS_ATTACHMENTS_DIRECTORY', 'commentions-attachments'),

        // Maximum size per file, in kilobytes.
        'max_size' => (int) env('COMMENTIONS_ATTACHMENTS_MAX_SIZE', 10240),

        // Maximum number of files per comment.
        'max_files' => (int) env('COMMENTIONS_ATTACHMENTS_MAX_FILES', 5),

        // Accepted MIME types, validated against the file's actual contents.
        // The defaults below cover common images and documents. Setting this
        // to an empty array allows ANY file type — avoid this on a public
        // disk, since it permits files browsers execute in-origin (such as
        // image/svg+xml or text/html) to be served from your app's URL.
        'accepted_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */
    'subscriptions' => [
        // When true, subscribed users will also receive the same event as mentions
        // (UserWasMentionedEvent). When false, a distinct
        // UserIsSubscribedToCommentableEvent will be dispatched instead.
        'dispatch_as_mention' => env('COMMENTIONS_SUBSCRIPTIONS_DISPATCH_AS_MENTION', false),
        // Controls whether the subscribers list is shown in the sidebar UI
        'show_subscribers' => env('COMMENTIONS_SUBSCRIPTIONS_SHOW_SUBSCRIBERS', true),
        'show_sidebar' => env('COMMENTIONS_SUBSCRIPTIONS_SHOW_SIDEBAR', true),
        // Automatically subscribe the author when they add a comment
        'auto_subscribe_on_comment' => env('COMMENTIONS_SUBSCRIPTIONS_AUTO_SUBSCRIBE_ON_COMMENT', true),
        // Automatically subscribe a user when they are mentioned in a comment
        'auto_subscribe_on_mention' => env('COMMENTIONS_SUBSCRIPTIONS_AUTO_SUBSCRIBE_ON_MENTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications (opt-in)
    |--------------------------------------------------------------------------
    |
    | Configure notification delivery when a user is mentioned in a comment.
    | Disabled by default; enable and choose the channels you want to use.
    |
    */
    'notifications' => [
        'mentions' => [
            'enabled' => env('COMMENTIONS_NOTIFICATIONS_MENTIONS_ENABLED', false),

            'channels' => explode(',', env('COMMENTIONS_NOTIFICATIONS_MENTIONS_CHANNELS', 'mail')),

            'listener' => SendUserMentionedNotification::class,
            'notification' => UserMentionedInComment::class,

            'mail' => [
                'subject' => env('COMMENTIONS_NOTIFICATIONS_MENTIONS_MAIL_SUBJECT', 'You were mentioned in a comment'),
            ],
        ],
    ],
];
