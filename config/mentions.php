<?php

return [
    'pools' => [
        App\Models\User::TABLE_NAME => [
            // Model that will be mentioned.
            'model' => App\Models\User::class,

            // The column that will be used to search the model by the parser.
            'column' => 'slug',

            // The route used to generate the user link.
            'route' => 'profile.details',

            // Notification class to use when this model is mentioned.
            'notification' => App\Notifications\NewUserMention::class,
        ],
        App\Models\Anime::TABLE_NAME => [
            // Model that will be mentioned.
            'model' => App\Models\Anime::class,

            // The column that will be used to search the model by the parser.
            'column' => 'slug',

            // The route used to generate the anime link.
            'route' => 'anime.details',

            // Notification class to use when this model is mentioned.
//            'notification' => App\Notifications\MentionNotification::class,
        ],
        App\Models\Character::TABLE_NAME => [
            // Model that will be mentioned.
            'model' => App\Models\Character::class,

            // The column that will be used to search the model by the parser.
            'column' => 'slug',

            // The route used to generate the anime link.
            'route' => '/characters/',

            // Notification class to use when this model is mentioned.
//            'notification' => App\Notifications\MentionNotification::class,
        ],
        App\Models\Person::TABLE_NAME => [
            // Model that will be mentioned.
            'model' => App\Models\Person::class,

            // The column that will be used to search the model by the parser.
            'column' => 'slug',

            // The route used to generate the anime link.
            'route' => '/people/',

            // Notification class to use when this model is mentioned.
//            'notification' => App\Notifications\MentionNotification::class,
        ],
        App\Models\Studio::TABLE_NAME => [
            // Model that will be mentioned.
            'model' => App\Models\Studio::class,

            // The column that will be used to search the model by the parser.
            'column' => 'slug',

            // The route used to generate the anime link.
            'route' => '/studios/',

            // Notification class to use when this model is mentioned.
//            'notification' => App\Notifications\MentionNotification::class,
        ]
    ]
];
