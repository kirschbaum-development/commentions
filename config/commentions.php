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
     * The comment config.
     */
    'comment' => [
        'policy' => \Kirschbaum\Commentions\Policies\CommentPolicy::class,
    ],
];
