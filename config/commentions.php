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

    'policy' => \Kirschbaum\Commentions\Policies\CommentPolicy::class,
];
