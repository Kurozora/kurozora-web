<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Locales
    |--------------------------------------------------------------------------
    |
    | Contains an array with the applications available locales.
    |
    */
    'locales' => [
        'ab',
        'aa',
        'af',
        'ak',
        'sq',
        'am',
        'ar',
        'an',
        'hy',
        'as',
        'av',
        'ae',
        'ay',
        'az',
        'bm',
        'ba',
        'eu',
        'be',
        'bn',
        'bh',
        'bi',
        'bs',
        'br',
        'bg',
        'my',
        'ca',
        'ch',
        'ce',
        'ny',
        'zh',
        'cv',
        'kw',
        'co',
        'cr',
        'hr',
        'cs',
        'da',
        'dv',
        'nl',
        'dz',
        'en',
        'eo',
        'et',
        'ee',
        'fo',
        'fj',
        'fi',
        'fr',
        'ff',
        'gl',
        'ka',
        'de',
        'el',
        'gn',
        'gu',
        'ht',
        'ha',
        'he',
        'hz',
        'hi',
        'ho',
        'hu',
        'ia',
        'id',
        'ie',
        'ga',
        'ig',
        'ik',
        'io',
        'is',
        'it',
        'iu',
        'ja',
        'jv',
        'kl',
        'kn',
        'kr',
        'ks',
        'kk',
        'km',
        'ki',
        'rw',
        'ky',
        'kv',
        'kg',
        'ko',
        'ku',
        'kj',
        'la',
        'lb',
        'lg',
        'li',
        'ln',
        'lo',
        'lt',
        'lu',
        'lv',
        'gv',
        'mk',
        'mg',
        'ms',
        'ml',
        'mt',
        'mi',
        'mr',
        'mh',
        'mn',
        'na',
        'nv',
        'nd',
        'ne',
        'ng',
        'nb',
        'nn',
        'no',
        'ii',
        'nr',
        'oc',
        'oj',
        'cu',
        'om',
        'or',
        'os',
        'pa',
        'pi',
        'fa',
        'pl',
        'ps',
        'pt',
        'qu',
        'rm',
        'rn',
        'ro',
        'ru',
        'sa',
        'sc',
        'sd',
        'se',
        'sm',
        'sg',
        'sr',
        'gd',
        'sn',
        'si',
        'sk',
        'sl',
        'so',
        'st',
        'es',
        'su',
        'sw',
        'ss',
        'sv',
        'ta',
        'te',
        'tg',
        'th',
        'ti',
        'bo',
        'tk',
        'tl',
        'tn',
        'to',
        'tr',
        'ts',
        'tt',
        'tw',
        'ty',
        'ug',
        'uk',
        'ur',
        'uz',
        've',
        'vi',
        'vo',
        'wa',
        'cy',
        'wo',
        'fy',
        'xh',
        'yi',
        'yo',
        'za',
        'zu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale separator
    |--------------------------------------------------------------------------
    |
    | This is a string used to glue the language and the country when defining
    | the available locales. Example: if set to '-', then the locale for
    | colombian spanish will be saved as 'es-CO' into the database.
    |
    */
    'locale_separator' => '-',

    /*
    |--------------------------------------------------------------------------
    | Default locale
    |--------------------------------------------------------------------------
    |
    | As a default locale, Translatable takes the locale of Laravel's
    | translator. If for some reason you want to override this,
    | you can specify what default should be used here.
    | If you set a value here it will only use the current config value
    | and never fallback to the translator one.
    |
    */
    'locale' => null,

    /*
    |--------------------------------------------------------------------------
    | Use fallback
    |--------------------------------------------------------------------------
    |
    | Determine if fallback locales are returned by default or not. To add
    | more flexibility and configure this option per "translatable"
    | instance, this value will be overridden by the property
    | $useTranslationFallback when defined
    |
    */
    'use_fallback' => true,

    /*
    |--------------------------------------------------------------------------
    | Use fallback per property
    |--------------------------------------------------------------------------
    |
    | The property fallback feature will return the translated value of
    | the fallback locale if the property is empty for the selected
    | locale. Note that 'use_fallback' must be enabled.
    |
     */
    'use_property_fallback' => true,

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | A fallback locale is the locale being used to return a translation
    | when the requested translation is not existing. To disable it
    | set it to false.
    | If set to null it will loop through all configured locales until
    | one existing is found or end of list reached. The locales are looped
    | from top to bottom and for country based locales the simple one
    | is used first. So "es" will be checked before "es_MX".
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Translation Model Namespace
    |--------------------------------------------------------------------------
    |
    | Defines the default 'Translation' class namespace. For example, if
    | you want to use App\Translations\CountryTranslation instead of App\CountryTranslation
    | set this to 'App\Translations'.
    |
    */
    'translation_model_namespace' => null,

    /*
    |--------------------------------------------------------------------------
    | Translation Suffix
    |--------------------------------------------------------------------------
    |
    | Defines the default 'Translation' class suffix. For example, if
    | you want to use CountryTrans instead of CountryTranslation
    | application, set this to 'Trans'.
    |
    */
    'translation_suffix' => 'Translation',

    /*
    |--------------------------------------------------------------------------
    | Locale key
    |--------------------------------------------------------------------------
    |
    | Defines the 'locale' field name, which is used by the
    | translation model.
    |
    */
    'locale_key' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Always load translations when converting to array
    |--------------------------------------------------------------------------
    | Setting this to false will have a performance improvement but will
    | not return the translations when using toArray(), unless the
    | translations relationship is already loaded.
    |
     */
    'to_array_always_loads_translations' => true,

    /*
    |--------------------------------------------------------------------------
    | Configure the default behavior of the rule factory
    |--------------------------------------------------------------------------
    | The default values used to control the behavior of the RuleFactory.
    | Here you can set your own default format and delimiters for
    | your whole app.
     *
     */
    'rule_factory' => [
        'format' => \Astrotomic\Translatable\Validation\RuleFactory::FORMAT_ARRAY,
        'prefix' => '%',
        'suffix' => '%',
    ],
];
