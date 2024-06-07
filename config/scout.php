<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "meilisearch", "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'algolia'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => false,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 100,
        'unsearchable' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records later.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application ID and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | MeiliSearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your MeiliSearch settings. MeiliSearch is an open
    | source search engine with minimal configuration. Below, you can state
    | the host and key information for your own MeiliSearch installation.
    |
    | See: https://docs.meilisearch.com/guides/advanced_guides/configuration.html
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            \App\Models\Anime::TABLE_NAME => [
                'searchableAttributes' => ['original_title', 'title', 'synonym_titles', 'translations', 'synopsis', 'tagline', 'tags'],
                'sortableAttributes'=> ['original_title', 'title', 'duration', 'air_time', 'air_day', 'air_season', 'is_nsfw', 'rank_total', 'started_at', 'ended_at', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'tv_rating_id', 'media_type_id', 'source_id', 'status_id', 'genres.id', 'themes.id', 'duration', 'air_time', 'air_day', 'air_season', 'is_nsfw', 'episode_count', 'season_count', 'started_at', 'ended_at'],
            ],
            \App\Models\AppTheme::TABLE_NAME => [
                'searchableAttributes' => ['name'],
                'sortableAttributes'=> ['download_count', 'name', 'version', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'download_count', 'name', 'ui_status_bar_style', 'version'],
            ],
            \App\Models\Character::TABLE_NAME => [
                'searchableAttributes' => ['name', 'nicknames', 'translations', 'about', 'short_description'],
                'sortableAttributes'=> ['age', 'astrological_sign', 'height', 'name', 'weight', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'age', 'astrological_sign', 'birth_day', 'birth_month', 'bust', 'height', 'hip', 'status', 'waist', 'weight'],
            ],
            \App\Models\Episode::TABLE_NAME => [
                'searchableAttributes' => ['title', 'translations', 'synopsis'],
                'sortableAttributes'=> ['duration', 'number', 'number_total', 'title', 'rank_total', 'started_at', 'ended_at', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'duration', 'is_filler', 'is_nsfw', 'is_special', 'is_premiere', 'is_finale', 'is_verified', 'number', 'number_total', 'season_id', 'tv_rating_id', 'started_at', 'ended_at'],
            ],
            \App\Models\Game::TABLE_NAME => [
                'searchableAttributes' => ['original_title', 'title', 'synonym_titles', 'translations', 'synopsis', 'tagline', 'tags'],
                'sortableAttributes'=> ['original_title', 'title', 'duration', 'publication_day', 'publication_season', 'is_nsfw', 'rank_total', 'edition_count', 'published_at', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'duration', 'tv_rating_id', 'media_type_id', 'source_id', 'status_id', 'genres.id', 'themes.id', 'publication_day', 'publication_season', 'is_nsfw', 'edition_count', 'published_at'],
            ],
            \App\Models\Manga::TABLE_NAME => [
                'searchableAttributes' => ['original_title', 'title', 'synonym_titles', 'translations', 'synopsis', 'tagline', 'tags'],
                'sortableAttributes'=> ['original_title', 'title', 'duration', 'publication_time', 'publication_day', 'publication_season', 'is_nsfw', 'rank_total', 'started_at', 'ended_at', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'duration', 'publication_time', 'publication_day', 'publication_season', 'is_nsfw', 'tv_rating_id', 'media_type_id', 'source_id', 'status_id', 'genres.id', 'themes.id', 'volume_count', 'chapter_count', 'page_count', 'started_at', 'ended_at'],
            ],
            \App\Models\Person::TABLE_NAME => [
                'searchableAttributes' => ['first_name', 'last_name', 'family_name', 'given_name', 'alternative_names', 'about', 'short_description'],
                'sortableAttributes'=> ['astrological_sign', 'birthdate', 'deceased_date', 'full_name', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'astrological_sign', 'birthdate', 'deceased_date'],
            ],
            \App\Models\Platform::TABLE_NAME => [
                'searchableAttributes' => ['original_name', 'name', 'synonym_names', 'translations', 'about', 'tagline',],
                'sortableAttributes'=> ['original_name', 'name', 'generation', 'type', 'rank_total', 'started_at', 'ended_at', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'generation', 'type', 'started_at', 'ended_at'],
            ],
            \App\Models\Song::TABLE_NAME => [
                'searchableAttributes' => ['original_title', 'title', 'artist', 'original_lyrics', 'lyrics', 'translations'],
                'sortableAttributes'=> ['artist', 'original_title', 'rank_total', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'artist'],
            ],
            \App\Models\Studio::TABLE_NAME => [
                'searchableAttributes' => ['name', 'address'],
                'sortableAttributes'=> ['address', 'founded', 'name', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'address', 'founded', 'is_nsfw', 'type'],
            ],
            \App\Models\UserLibrary::TABLE_NAME => [
                'searchableAttributes' => ['trackable.original_title', 'trackable.title', 'trackable.synonym_titles', 'trackable.translations', 'trackable.synopsis', 'trackable.tagline'],
                'sortableAttributes'=> ['ended_at', 'started_at', 'status', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'trackable_id', 'trackable_type', 'started_at', 'ended_at', 'status', 'user_id'],
            ],
            \App\Models\User::TABLE_NAME => [
                'searchableAttributes' => ['slug', 'username', 'biography'],
                'sortableAttributes'=> ['slug', 'username', 'created_at', 'update_at'],
                'filterableAttributes'=> ['id', 'is_developer', 'is_staff', 'is_early_supporter', 'is_pro', 'is_subscribed', 'subscribed_at', 'created_at'],
            ],
        ],
    ],

];
