<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Render Profile
    |--------------------------------------------------------------------------
    |
    | Controls the output format. Options: 'html', 'email', 'plain'
    |
    */
    'profile' => 'html',

    /*
    |--------------------------------------------------------------------------
    | Resolvers
    |--------------------------------------------------------------------------
    |
    | Class names for mention and embed resolvers. Set to null to disable
    | resolution (mentions render as plain spans, embeds render as links).
    | Classes are resolved from the container.
    |
    */
    'resolvers' => [
        'mention' => null,
        'embed' => null,
    ],
];
