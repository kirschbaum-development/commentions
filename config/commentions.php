<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table name configurations
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'comments' => 'comments',
        'comment_reactions' => 'comment_reactions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commenter model configuration
    |--------------------------------------------------------------------------
    */
    'commenter' => [
        'model' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Comment model configuration
    |--------------------------------------------------------------------------
    */
    'comment' => [
        'model' => \Kirschbaum\Commentions\Comment::class,
        'policy' => \Kirschbaum\Commentions\Policies\CommentPolicy::class,
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
    | Notifications (opt-in)
    |--------------------------------------------------------------------------
    |
    | Configure notification delivery when a user is mentioned in a comment.
    | Disabled by default; enable and choose the channels you want to use.
    |
    */
    'notifications' => [
        'mentions' => [
            'enabled' => false,

            'channels' => ['mail'],

            'listener' => \Kirschbaum\Commentions\Listeners\SendUserMentionedNotification::class,
            'notification' => \Kirschbaum\Commentions\Notifications\UserMentionedInComment::class,

            'mail' => [
                'subject' => 'You were mentioned in a comment',
            ],
        ],
    ],
];
