<?php

return [
    'path' => 'artisan',

    'commands' => [
        'Up' => [
            'run' => 'up',
            'type' => 'success',
            'group' => 'Maintenance',
        ],

        'Down' => [
            'run' => 'down --secret {secret phrase}',
            'type' => 'secondary',
            'group' => 'Maintenance',
            'variables' => [
                [
                    'label' =>  'secret phrase',
                    'field' => 'text',
                ]
            ]
        ],

    ],

    // Limit the command run history to latest 10 runs
    'history'  => 10,

    // Tool name displayed in the navigation menu
    'navigation_label' => 'Artisan',

    // Any additional info to display on the tool page. Can contain string and html.
    'help' => '',

    // Allow running of custom artisan and bash(shell) commands
    // Options: artisan, bash
    'custom_commands' => ['artisan'],

    'without_overlapping' => [
        // Blocks running commands simultaneously under the given groups. Use '*' for block all groups
        'groups' => [
            //
        ],

        // Blocks running commands simultaneously. Use '*' for block all groups
        'commands' => [
            //
        ],
    ],

    // Polling commands history. Turns on automatically when you run commands with the progressbar
    'polling_time' => 2500,
];
