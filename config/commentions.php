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
        'comment_subscriptions' => 'comment_subscriptions',
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
    | Subscriptions
    |--------------------------------------------------------------------------
    */
    'subscriptions' => [
        // When true, subscribed users will also receive the same event as mentions
        // (UserWasMentionedEvent). When false, a distinct
        // UserIsSubscribedToCommentableEvent will be dispatched instead.
        'dispatch_as_mention' => false,
        // Controls whether the subscribers list is shown in the sidebar UI
        'show_subscribers' => true,
        // Automatically subscribe the author when they add a comment
        'auto_subscribe_on_comment' => true,
        // Automatically subscribe a user when they are mentioned in a comment
        'auto_subscribe_on_mention' => true,
    ],
];
