<?php

return [

    /**
     * The author of the log messages. You can set both to null to keep the Webhook author set in Discord
     */
    'from' => [
        'name'       => null,
        'avatar_url' => null,
    ],

    /**
     * The converter to use to turn a log record into a discord message
     *
     * Bundled converters:
     * - \MarvinLabs\DiscordLogger\Converters\SimpleRecordConverter::class
     * - \MarvinLabs\DiscordLogger\Converters\RichRecordConverter::class
     */
    'converter'  => \MarvinLabs\DiscordLogger\Converters\RichRecordConverter::class,

    /**
     * If enabled, stacktraces will be attached as files. If not, stacktraces will be directly printed out in the
     * message.
     *
     * Valid values are:
     *
     * - 'smart': when stacktrace is less than 2000 characters, it is inlined with the message, else attached as file
     * - 'file': stacktrace is always attached as file
     * - 'inline': stacktrace is always inlined with the message, truncated if necessary
     */
    'stacktrace' => 'smart',

    /**
     * A set of colors to associate to the different log levels when using the `RichRecordConverter`
     */
    'colors'     => [
        'DEBUG'     => 0x8B5CF6,
        'INFO'      => 0x0EA5E9,
        'NOTICE'    => 0x9B9B9B,
        'WARNING'   => 0xFACC15,
        'ERROR'     => 0xF43F5E,
        'CRITICAL'  => 0xDC2626,
        'ALERT'     => 0xF59E0B,
        'EMERGENCY' => 0xCC0000,
    ],

    /**
     * A set of emojis to associate to the different log levels. Set to null to disable an emoji for a given level
     */
    'emojis'     => [
        'DEBUG'     => ':lady_beetle:',
        'INFO'      => ':information_source:',
        'NOTICE'    => ':scroll:',
        'WARNING'   => ':warning:',
        'ERROR'     => ':x:',
        'CRITICAL'  => ':boom:',
        'ALERT'     => ':rotating_light:',
        'EMERGENCY' => ':skull:',
    ],
];
