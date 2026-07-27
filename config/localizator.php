<?php

return [

    /**
     * Localize types of translation strings.
     */
    'localize' => [
        /**
         * Short keys, stored in PHP files inside folders named by their locale code.
         */
        'default' => true,

        /**
         * Translation strings as key, stored in a JSON file for each locale.
         */
        'json' => true,
    ],

    /**
     * Search criteria for files.
     */
    'search' => [
        /**
         * Directories which should be looked inside.
         *
         * `app` is required: translation calls in models, Livewire components, services and
         * controllers are user-facing, and keys the scanner never sees are pruned on every run.
         */
        'dirs' => ['resources/views', 'app'],

        /**
         * Subdirectories which will be excluded.
         * The values must be relative to the included directory paths.
         */
        'exclude' => [
            //
        ],

        /**
         * Patterns by which files should be queried.
         */
        'patterns' => ['*.php'],

        /**
         * Functions that the strings will be extracted from.
         * The translation string must always be the first argument, written as a literal —
         * a concatenated or interpolated key is invisible here and gets pruned.
         */
        'functions' => ['__', 'trans', '@lang'],
    ],

    /**
     * Should the localize command sort extracted strings alphabetically?
     */
    'sort' => true,

];
