<?php

return [
    /**
     * The table name.
     */
    'table_name' => 'comments',

    /**
     * The commenter config.
     */
    'commenter' => [
        'model' => \App\Models\User::class,
    ],

    /**
     * Comment editing/deleting options.
     */
    'allow_edits' => true,
    'allow_deletes' => true,

    /**
     * Allowed reactions for comments.
     * Define the list of emojis that users can react with.
     */
    'allowed_reactions' => ['👍', '❤️', '😂', '😮', '😢', '🤔'],
];
