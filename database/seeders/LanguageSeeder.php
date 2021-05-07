<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * The available languages.
     *
     * @var array $languages
     */
    protected array $languages = [
        [
            'name' => 'Abkhazian',
            'code' => 'ab',
        ],
        [
            'name' => 'Afar',
            'code' => 'aa',
        ],
        [
            'name' => 'Afrikaans',
            'code' => 'af',
        ],
        [
            'name' => 'Akan',
            'code' => 'ak',
        ],
        [
            'name' => 'Albanian',
            'code' => 'sq',
        ],
        [
            'name' => 'Amharic',
            'code' => 'am',
        ],
        [
            'name' => 'Arabic',
            'code' => 'ar',
        ],
        [
            'name' => 'Aragonese',
            'code' => 'an',
        ],
        [
            'name' => 'Armenian',
            'code' => 'hy',
        ],
        [
            'name' => 'Assamese',
            'code' => 'as',
        ],
        [
            'name' => 'Avaric',
            'code' => 'av',
        ],
        [
            'name' => 'Avestan',
            'code' => 'ae',
        ],
        [
            'name' => 'Aymara',
            'code' => 'ay',
        ],
        [
            'name' => 'Azerbaijani',
            'code' => 'az',
        ],
        [
            'name' => 'Bambara',
            'code' => 'bm',
        ],
        [
            'name' => 'Bashkir',
            'code' => 'ba',
        ],
        [
            'name' => 'Basque',
            'code' => 'eu',
        ],
        [
            'name' => 'Belarusian',
            'code' => 'be',
        ],
        [
            'name' => 'Bengali',
            'code' => 'bn',
        ],
        [
            'name' => 'Bihari languages',
            'code' => 'bh',
        ],
        [
            'name' => 'Bislama',
            'code' => 'bi',
        ],
        [
            'name' => 'Bosnian',
            'code' => 'bs',
        ],
        [
            'name' => 'Breton',
            'code' => 'br',
        ],
        [
            'name' => 'Bulgarian',
            'code' => 'bg',
        ],
        [
            'name' => 'Burmese',
            'code' => 'my',
        ],
        [
            'name' => 'Catalan',
            'code' => 'ca',
        ],
        [
            'name' => 'Chamorro',
            'code' => 'ch',
        ],
        [
            'name' => 'Chechen',
            'code' => 'ce',
        ],
        [
            'name' => 'Chichewa',
            'code' => 'ny',
        ],
        [
            'name' => 'Chinese',
            'code' => 'zh',
        ],
        [
            'name' => 'Chuvash',
            'code' => 'cv',
        ],
        [
            'name' => 'Cornish',
            'code' => 'kw',
        ],
        [
            'name' => 'Corsican',
            'code' => 'co',
        ],
        [
            'name' => 'Cree',
            'code' => 'cr',
        ],
        [
            'name' => 'Croatian',
            'code' => 'hr',
        ],
        [
            'name' => 'Czech',
            'code' => 'cs',
        ],
        [
            'name' => 'Danish',
            'code' => 'da',
        ],
        [
            'name' => 'Divehi',
            'code' => 'dv',
        ],
        [
            'name' => 'Dutch',
            'code' => 'nl',
        ],
        [
            'name' => 'Dzongkha',
            'code' => 'dz',
        ],
        [
            'name' => 'English',
            'code' => 'en',
        ],
        [
            'name' => 'Esperanto',
            'code' => 'eo',
        ],
        [
            'name' => 'Estonian',
            'code' => 'et',
        ],
        [
            'name' => 'Ewe',
            'code' => 'ee',
        ],
        [
            'name' => 'Faroese',
            'code' => 'fo',
        ],
        [
            'name' => 'Fijian',
            'code' => 'fj',
        ],
        [
            'name' => 'Finnish',
            'code' => 'fi',
        ],
        [
            'name' => 'French',
            'code' => 'fr',
        ],
        [
            'name' => 'Fulah',
            'code' => 'ff',
        ],
        [
            'name' => 'Galician',
            'code' => 'gl',
        ],
        [
            'name' => 'Georgian',
            'code' => 'ka',
        ],
        [
            'name' => 'German',
            'code' => 'de',
        ],
        [
            'name' => 'Greek',
            'code' => 'el',
        ],
        [
            'name' => 'Guarani',
            'code' => 'gn',
        ],
        [
            'name' => 'Gujarati',
            'code' => 'gu',
        ],
        [
            'name' => 'Haitian',
            'code' => 'ht',
        ],
        [
            'name' => 'Hausa',
            'code' => 'ha',
        ],
        [
            'name' => 'Hebrew',
            'code' => 'he',
        ],
        [
            'name' => 'Herero',
            'code' => 'hz',
        ],
        [
            'name' => 'Hindi',
            'code' => 'hi',
        ],
        [
            'name' => 'Hiri Motu',
            'code' => 'ho',
        ],
        [
            'name' => 'Hungarian',
            'code' => 'hu',
        ],
        [
            'name' => 'Interlingua',
            'code' => 'ia',
        ],
        [
            'name' => 'Indonesian',
            'code' => 'id',
        ],
        [
            'name' => 'Interlingue',
            'code' => 'ie',
        ],
        [
            'name' => 'Irish',
            'code' => 'ga',
        ],
        [
            'name' => 'Igbo',
            'code' => 'ig',
        ],
        [
            'name' => 'Inupiaq',
            'code' => 'ik',
        ],
        [
            'name' => 'Ido',
            'code' => 'io',
        ],
        [
            'name' => 'Icelandic',
            'code' => 'is',
        ],
        [
            'name' => 'Italian',
            'code' => 'it',
        ],
        [
            'name' => 'Inuktitut',
            'code' => 'iu',
        ],
        [
            'name' => 'Japanese',
            'code' => 'ja',
        ],
        [
            'name' => 'Javanese',
            'code' => 'jv',
        ],
        [
            'name' => 'Kalaallisut',
            'code' => 'kl',
        ],
        [
            'name' => 'Kannada',
            'code' => 'kn',
        ],
        [
            'name' => 'Kanuri',
            'code' => 'kr',
        ],
        [
            'name' => 'Kashmiri',
            'code' => 'ks',
        ],
        [
            'name' => 'Kazakh',
            'code' => 'kk',
        ],
        [
            'name' => 'Central Khmer',
            'code' => 'km',
        ],
        [
            'name' => 'Kikuyu',
            'code' => 'ki',
        ],
        [
            'name' => 'Kinyarwanda',
            'code' => 'rw',
        ],
        [
            'name' => 'Kirghiz',
            'code' => 'ky',
        ],
        [
            'name' => 'Komi',
            'code' => 'kv',
        ],
        [
            'name' => 'Kongo',
            'code' => 'kg',
        ],
        [
            'name' => 'Korean',
            'code' => 'ko',
        ],
        [
            'name' => 'Kurdish',
            'code' => 'ku',
        ],
        [
            'name' => 'Kuanyama',
            'code' => 'kj',
        ],
        [
            'name' => 'Latin',
            'code' => 'la',
        ],
        [
            'name' => 'Luxembourgish',
            'code' => 'lb',
        ],
        [
            'name' => 'Ganda',
            'code' => 'lg',
        ],
        [
            'name' => 'Limburgan',
            'code' => 'li',
        ],
        [
            'name' => 'Lingala',
            'code' => 'ln',
        ],
        [
            'name' => 'Lao',
            'code' => 'lo',
        ],
        [
            'name' => 'Lithuanian',
            'code' => 'lt',
        ],
        [
            'name' => 'Luba-Katanga',
            'code' => 'lu',
        ],
        [
            'name' => 'Latvian',
            'code' => 'lv',
        ],
        [
            'name' => 'Manx',
            'code' => 'gv',
        ],
        [
            'name' => 'Macedonian',
            'code' => 'mk',
        ],
        [
            'name' => 'Malagasy',
            'code' => 'mg',
        ],
        [
            'name' => 'Malay',
            'code' => 'ms',
        ],
        [
            'name' => 'Malayalam',
            'code' => 'ml',
        ],
        [
            'name' => 'Maltese',
            'code' => 'mt',
        ],
        [
            'name' => 'Māori',
            'code' => 'mi',
        ],
        [
            'name' => 'Marathi',
            'code' => 'mr',
        ],
        [
            'name' => 'Marshallese',
            'code' => 'mh',
        ],
        [
            'name' => 'Mongolian',
            'code' => 'mn',
        ],
        [
            'name' => 'Nauru',
            'code' => 'na',
        ],
        [
            'name' => 'Navajo',
            'code' => 'nv',
        ],
        [
            'name' => 'North Ndebele',
            'code' => 'nd',
        ],
        [
            'name' => 'Nepali',
            'code' => 'ne',
        ],
        [
            'name' => 'Ndonga',
            'code' => 'ng',
        ],
        [
            'name' => 'Norwegian Bokmål',
            'code' => 'nb',
        ],
        [
            'name' => 'Norwegian Nynorsk',
            'code' => 'nn',
        ],
        [
            'name' => 'Norwegian',
            'code' => 'no',
        ],
        [
            'name' => 'Sichuan Yi',
            'code' => 'ii',
        ],
        [
            'name' => 'South Ndebele',
            'code' => 'nr',
        ],
        [
            'name' => 'Occitan',
            'code' => 'oc',
        ],
        [
            'name' => 'Ojibwa',
            'code' => 'oj',
        ],
        [
            'name' => 'Church Slavic',
            'code' => 'cu',
        ],
        [
            'name' => 'Oromo',
            'code' => 'om',
        ],
        [
            'name' => 'Oriya',
            'code' => 'or',
        ],
        [
            'name' => 'Ossetian',
            'code' => 'os',
        ],
        [
            'name' => 'Punjabi',
            'code' => 'pa',
        ],
        [
            'name' => 'Pali',
            'code' => 'pi',
        ],
        [
            'name' => 'Persian',
            'code' => 'fa',
        ],
        [
            'name' => 'Polish',
            'code' => 'pl',
        ],
        [
            'name' => 'Pashto',
            'code' => 'ps',
        ],
        [
            'name' => 'Portuguese',
            'code' => 'pt',
        ],
        [
            'name' => 'Quechua',
            'code' => 'qu',
        ],
        [
            'name' => 'Romansh',
            'code' => 'rm',
        ],
        [
            'name' => 'Rundi',
            'code' => 'rn',
        ],
        [
            'name' => 'Romanian',
            'code' => 'ro',
        ],
        [
            'name' => 'Russian',
            'code' => 'ru',
        ],
        [
            'name' => 'Sanskrit',
            'code' => 'sa',
        ],
        [
            'name' => 'Sardinian',
            'code' => 'sc',
        ],
        [
            'name' => 'Sindhi',
            'code' => 'sd',
        ],
        [
            'name' => 'Northern Sami',
            'code' => 'se',
        ],
        [
            'name' => 'Samoan',
            'code' => 'sm',
        ],
        [
            'name' => 'Sango',
            'code' => 'sg',
        ],
        [
            'name' => 'Serbian',
            'code' => 'sr',
        ],
        [
            'name' => 'Gaelic',
            'code' => 'gd',
        ],
        [
            'name' => 'Shona',
            'code' => 'sn',
        ],
        [
            'name' => 'Sinhala',
            'code' => 'si',
        ],
        [
            'name' => 'Slovak',
            'code' => 'sk',
        ],
        [
            'name' => 'Slovenian',
            'code' => 'sl',
        ],
        [
            'name' => 'Somali',
            'code' => 'so',
        ],
        [
            'name' => 'Southern Sotho',
            'code' => 'st',
        ],
        [
            'name' => 'Spanish',
            'code' => 'es',
        ],
        [
            'name' => 'Sundanese',
            'code' => 'su',
        ],
        [
            'name' => 'Swahili',
            'code' => 'sw',
        ],
        [
            'name' => 'Swati',
            'code' => 'ss',
        ],
        [
            'name' => 'Swedish',
            'code' => 'sv',
        ],
        [
            'name' => 'Tamil',
            'code' => 'ta',
        ],
        [
            'name' => 'Telugu',
            'code' => 'te',
        ],
        [
            'name' => 'Tajik',
            'code' => 'tg',
        ],
        [
            'name' => 'Thai',
            'code' => 'th',
        ],
        [
            'name' => 'Tigrinya',
            'code' => 'ti',
        ],
        [
            'name' => 'Tibetan',
            'code' => 'bo',
        ],
        [
            'name' => 'Turkmen',
            'code' => 'tk',
        ],
        [
            'name' => 'Tagalog',
            'code' => 'tl',
        ],
        [
            'name' => 'Tswana',
            'code' => 'tn',
        ],
        [
            'name' => 'Tonga (Tonga Islands)',
            'code' => 'to',
        ],
        [
            'name' => 'Turkish',
            'code' => 'tr',
        ],
        [
            'name' => 'Tsonga',
            'code' => 'ts',
        ],
        [
            'name' => 'Tatar',
            'code' => 'tt',
        ],
        [
            'name' => 'Twi',
            'code' => 'tw',
        ],
        [
            'name' => 'Tahitian',
            'code' => 'ty',
        ],
        [
            'name' => 'Uighur',
            'code' => 'ug',
        ],
        [
            'name' => 'Ukrainian',
            'code' => 'uk',
        ],
        [
            'name' => 'Urdu',
            'code' => 'ur',
        ],
        [
            'name' => 'Uzbek',
            'code' => 'uz',
        ],
        [
            'name' => 'Venda',
            'code' => 've',
        ],
        [
            'name' => 'Vietnamese',
            'code' => 'vi',
        ],
        [
            'name' => 'Volapük',
            'code' => 'vo',
        ],
        [
            'name' => 'Walloon',
            'code' => 'wa',
        ],
        [
            'name' => 'Welsh',
            'code' => 'cy',
        ],
        [
            'name' => 'Wolof',
            'code' => 'wo',
        ],
        [
            'name' => 'Western Frisian',
            'code' => 'fy',
        ],
        [
            'name' => 'Xhosa',
            'code' => 'xh',
        ],
        [
            'name' => 'Yiddish',
            'code' => 'yi',
        ],
        [
            'name' => 'Yoruba',
            'code' => 'yo',
        ],
        [
            'name' => 'Zhuang, Chuang',
            'code' => 'za',
        ],
        [
            'name' => 'Zulu',
            'code' => 'zu',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->languages as $language) {
            Language::create($language);
        }
    }
}
