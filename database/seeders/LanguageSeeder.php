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
            'iso_639_3' => 'abk',
        ],
        [
            'name' => 'Afar',
            'code' => 'aa',
            'iso_639_3' => 'aar',
        ],
        [
            'name' => 'Afrikaans',
            'code' => 'af',
            'iso_639_3' => 'afr',
        ],
        [
            'name' => 'Akan',
            'code' => 'ak',
            'iso_639_3' => 'aka',
        ],
        [
            'name' => 'Albanian',
            'code' => 'sq',
            'iso_639_3' => 'sqi',
        ],
        [
            'name' => 'Amharic',
            'code' => 'am',
            'iso_639_3' => 'amh',
        ],
        [
            'name' => 'Arabic',
            'code' => 'ar',
            'iso_639_3' => 'ara',
        ],
        [
            'name' => 'Aragonese',
            'code' => 'an',
            'iso_639_3' => 'arg',
        ],
        [
            'name' => 'Armenian',
            'code' => 'hy',
            'iso_639_3' => 'hye',
        ],
        [
            'name' => 'Assamese',
            'code' => 'as',
            'iso_639_3' => 'asm',
        ],
        [
            'name' => 'Avaric',
            'code' => 'av',
            'iso_639_3' => 'ava',
        ],
        [
            'name' => 'Avestan',
            'code' => 'ae',
            'iso_639_3' => 'ave',
        ],
        [
            'name' => 'Aymara',
            'code' => 'ay',
            'iso_639_3' => 'aym',
        ],
        [
            'name' => 'Azerbaijani',
            'code' => 'az',
            'iso_639_3' => 'aze',
        ],
        [
            'name' => 'Bambara',
            'code' => 'bm',
            'iso_639_3' => 'bam',
        ],
        [
            'name' => 'Bashkir',
            'code' => 'ba',
            'iso_639_3' => 'bak',
        ],
        [
            'name' => 'Basque',
            'code' => 'eu',
            'iso_639_3' => 'eus',
        ],
        [
            'name' => 'Belarusian',
            'code' => 'be',
            'iso_639_3' => 'bel',
        ],
        [
            'name' => 'Bengali',
            'code' => 'bn',
            'iso_639_3' => 'ben',
        ],
        [
            'name' => 'Bihari languages',
            'code' => 'bh',
            'iso_639_3' => 'bih',
        ],
        [
            'name' => 'Bislama',
            'code' => 'bi',
            'iso_639_3' => 'bis',
        ],
        [
            'name' => 'Bosnian',
            'code' => 'bs',
            'iso_639_3' => 'bos',
        ],
        [
            'name' => 'Breton',
            'code' => 'br',
            'iso_639_3' => 'bre',
        ],
        [
            'name' => 'Bulgarian',
            'code' => 'bg',
            'iso_639_3' => 'bul',
        ],
        [
            'name' => 'Burmese',
            'code' => 'my',
            'iso_639_3' => 'mya',
        ],
        [
            'name' => 'Catalan',
            'code' => 'ca',
            'iso_639_3' => 'cat',
        ],
        [
            'name' => 'Chamorro',
            'code' => 'ch',
            'iso_639_3' => 'cha',
        ],
        [
            'name' => 'Chechen',
            'code' => 'ce',
            'iso_639_3' => 'che',
        ],
        [
            'name' => 'Chichewa',
            'code' => 'ny',
            'iso_639_3' => 'nya',
        ],
        [
            'name' => 'Chinese',
            'code' => 'zh',
            'iso_639_3' => 'zho',
        ],
        [
            'name' => 'Chuvash',
            'code' => 'cv',
            'iso_639_3' => 'chv',
        ],
        [
            'name' => 'Cornish',
            'code' => 'kw',
            'iso_639_3' => 'cor',
        ],
        [
            'name' => 'Corsican',
            'code' => 'co',
            'iso_639_3' => 'cos',
        ],
        [
            'name' => 'Cree',
            'code' => 'cr',
            'iso_639_3' => 'cre',
        ],
        [
            'name' => 'Croatian',
            'code' => 'hr',
            'iso_639_3' => 'hrv',
        ],
        [
            'name' => 'Czech',
            'code' => 'cs',
            'iso_639_3' => 'ces',
        ],
        [
            'name' => 'Danish',
            'code' => 'da',
            'iso_639_3' => 'dan',
        ],
        [
            'name' => 'Divehi',
            'code' => 'dv',
            'iso_639_3' => 'div',
        ],
        [
            'name' => 'Dutch',
            'code' => 'nl',
            'iso_639_3' => 'nld',
        ],
        [
            'name' => 'Dzongkha',
            'code' => 'dz',
            'iso_639_3' => 'dzo',
        ],
        [
            'name' => 'English',
            'code' => 'en',
            'iso_639_3' => 'eng',
        ],
        [
            'name' => 'Esperanto',
            'code' => 'eo',
            'iso_639_3' => 'epo',
        ],
        [
            'name' => 'Estonian',
            'code' => 'et',
            'iso_639_3' => 'est',
        ],
        [
            'name' => 'Ewe',
            'code' => 'ee',
            'iso_639_3' => 'ewe',
        ],
        [
            'name' => 'Faroese',
            'code' => 'fo',
            'iso_639_3' => 'fao',
        ],
        [
            'name' => 'Fijian',
            'code' => 'fj',
            'iso_639_3' => 'fij',
        ],
        [
            'name' => 'Finnish',
            'code' => 'fi',
            'iso_639_3' => 'fin',
        ],
        [
            'name' => 'French',
            'code' => 'fr',
            'iso_639_3' => 'fra',
        ],
        [
            'name' => 'Fulah',
            'code' => 'ff',
            'iso_639_3' => 'ful',
        ],
        [
            'name' => 'Galician',
            'code' => 'gl',
            'iso_639_3' => 'glg',
        ],
        [
            'name' => 'Georgian',
            'code' => 'ka',
            'iso_639_3' => 'kat',
        ],
        [
            'name' => 'German',
            'code' => 'de',
            'iso_639_3' => 'deu',
        ],
        [
            'name' => 'Greek',
            'code' => 'el',
            'iso_639_3' => 'ell',
        ],
        [
            'name' => 'Guarani',
            'code' => 'gn',
            'iso_639_3' => 'grn',
        ],
        [
            'name' => 'Gujarati',
            'code' => 'gu',
            'iso_639_3' => 'guj',
        ],
        [
            'name' => 'Haitian',
            'code' => 'ht',
            'iso_639_3' => 'hat',
        ],
        [
            'name' => 'Hausa',
            'code' => 'ha',
            'iso_639_3' => 'hau',
        ],
        [
            'name' => 'Hebrew',
            'code' => 'he',
            'iso_639_3' => 'heb',
        ],
        [
            'name' => 'Herero',
            'code' => 'hz',
            'iso_639_3' => 'her',
        ],
        [
            'name' => 'Hindi',
            'code' => 'hi',
            'iso_639_3' => 'hin',
        ],
        [
            'name' => 'Hiri Motu',
            'code' => 'ho',
            'iso_639_3' => 'hmo',
        ],
        [
            'name' => 'Hungarian',
            'code' => 'hu',
            'iso_639_3' => 'hun',
        ],
        [
            'name' => 'Interlingua',
            'code' => 'ia',
            'iso_639_3' => 'ina',
        ],
        [
            'name' => 'Indonesian',
            'code' => 'id',
            'iso_639_3' => 'ind',
        ],
        [
            'name' => 'Interlingue',
            'code' => 'ie',
            'iso_639_3' => 'ile',
        ],
        [
            'name' => 'Irish',
            'code' => 'ga',
            'iso_639_3' => 'gle',
        ],
        [
            'name' => 'Igbo',
            'code' => 'ig',
            'iso_639_3' => 'ibo',
        ],
        [
            'name' => 'Inupiaq',
            'code' => 'ik',
            'iso_639_3' => 'ipk',
        ],
        [
            'name' => 'Ido',
            'code' => 'io',
            'iso_639_3' => 'ido',
        ],
        [
            'name' => 'Icelandic',
            'code' => 'is',
            'iso_639_3' => 'isl',
        ],
        [
            'name' => 'Italian',
            'code' => 'it',
            'iso_639_3' => 'ita',
        ],
        [
            'name' => 'Inuktitut',
            'code' => 'iu',
            'iso_639_3' => 'iku',
        ],
        [
            'name' => 'Japanese',
            'code' => 'ja',
            'iso_639_3' => 'jpn',
        ],
        [
            'name' => 'Javanese',
            'code' => 'jv',
            'iso_639_3' => 'jav',
        ],
        [
            'name' => 'Kalaallisut',
            'code' => 'kl',
            'iso_639_3' => 'kal',
        ],
        [
            'name' => 'Kannada',
            'code' => 'kn',
            'iso_639_3' => 'kan',
        ],
        [
            'name' => 'Kanuri',
            'code' => 'kr',
            'iso_639_3' => 'kau',
        ],
        [
            'name' => 'Kashmiri',
            'code' => 'ks',
            'iso_639_3' => 'kas',
        ],
        [
            'name' => 'Kazakh',
            'code' => 'kk',
            'iso_639_3' => 'kaz',
        ],
        [
            'name' => 'Central Khmer',
            'code' => 'km',
            'iso_639_3' => 'khm',
        ],
        [
            'name' => 'Kikuyu',
            'code' => 'ki',
            'iso_639_3' => 'kik',
        ],
        [
            'name' => 'Kinyarwanda',
            'code' => 'rw',
            'iso_639_3' => 'kin',
        ],
        [
            'name' => 'Kirghiz',
            'code' => 'ky',
            'iso_639_3' => 'kir',
        ],
        [
            'name' => 'Komi',
            'code' => 'kv',
            'iso_639_3' => 'kom',
        ],
        [
            'name' => 'Kongo',
            'code' => 'kg',
            'iso_639_3' => 'kon',
        ],
        [
            'name' => 'Korean',
            'code' => 'ko',
            'iso_639_3' => 'kor',
        ],
        [
            'name' => 'Kurdish',
            'code' => 'ku',
            'iso_639_3' => 'kur',
        ],
        [
            'name' => 'Kuanyama',
            'code' => 'kj',
            'iso_639_3' => 'kua',
        ],
        [
            'name' => 'Latin',
            'code' => 'la',
            'iso_639_3' => 'lat',
        ],
        [
            'name' => 'Luxembourgish',
            'code' => 'lb',
            'iso_639_3' => 'ltz',
        ],
        [
            'name' => 'Ganda',
            'code' => 'lg',
            'iso_639_3' => 'lug',
        ],
        [
            'name' => 'Limburgan',
            'code' => 'li',
            'iso_639_3' => 'lim',
        ],
        [
            'name' => 'Lingala',
            'code' => 'ln',
            'iso_639_3' => 'lin',
        ],
        [
            'name' => 'Lao',
            'code' => 'lo',
            'iso_639_3' => 'lao',
        ],
        [
            'name' => 'Lithuanian',
            'code' => 'lt',
            'iso_639_3' => 'lit',
        ],
        [
            'name' => 'Luba-Katanga',
            'code' => 'lu',
            'iso_639_3' => 'lub',
        ],
        [
            'name' => 'Latvian',
            'code' => 'lv',
            'iso_639_3' => 'lav',
        ],
        [
            'name' => 'Manx',
            'code' => 'gv',
            'iso_639_3' => 'glv',
        ],
        [
            'name' => 'Macedonian',
            'code' => 'mk',
            'iso_639_3' => 'mkd',
        ],
        [
            'name' => 'Malagasy',
            'code' => 'mg',
            'iso_639_3' => 'mlg',
        ],
        [
            'name' => 'Malay',
            'code' => 'ms',
            'iso_639_3' => 'msa',
        ],
        [
            'name' => 'Malayalam',
            'code' => 'ml',
            'iso_639_3' => 'mal',
        ],
        [
            'name' => 'Maltese',
            'code' => 'mt',
            'iso_639_3' => 'mlt',
        ],
        [
            'name' => 'Māori',
            'code' => 'mi',
            'iso_639_3' => 'mri',
        ],
        [
            'name' => 'Marathi',
            'code' => 'mr',
            'iso_639_3' => 'mar',
        ],
        [
            'name' => 'Marshallese',
            'code' => 'mh',
            'iso_639_3' => 'mah',
        ],
        [
            'name' => 'Mongolian',
            'code' => 'mn',
            'iso_639_3' => 'mon',
        ],
        [
            'name' => 'Nauru',
            'code' => 'na',
            'iso_639_3' => 'nau',
        ],
        [
            'name' => 'Navajo',
            'code' => 'nv',
            'iso_639_3' => 'nav',
        ],
        [
            'name' => 'North Ndebele',
            'code' => 'nd',
            'iso_639_3' => 'nde',
        ],
        [
            'name' => 'Nepali',
            'code' => 'ne',
            'iso_639_3' => 'nep',
        ],
        [
            'name' => 'Ndonga',
            'code' => 'ng',
            'iso_639_3' => 'ndo',
        ],
        [
            'name' => 'Norwegian Bokmål',
            'code' => 'nb',
            'iso_639_3' => 'nob',
        ],
        [
            'name' => 'Norwegian Nynorsk',
            'code' => 'nn',
            'iso_639_3' => 'nno',
        ],
        [
            'name' => 'Norwegian',
            'code' => 'no',
            'iso_639_3' => 'nor',
        ],
        [
            'name' => 'Sichuan Yi',
            'code' => 'ii',
            'iso_639_3' => 'iii',
        ],
        [
            'name' => 'South Ndebele',
            'code' => 'nr',
            'iso_639_3' => 'nbl',
        ],
        [
            'name' => 'Occitan',
            'code' => 'oc',
            'iso_639_3' => 'oci',
        ],
        [
            'name' => 'Ojibwa',
            'code' => 'oj',
            'iso_639_3' => 'oji',
        ],
        [
            'name' => 'Church Slavic',
            'code' => 'cu',
            'iso_639_3' => 'chu',
        ],
        [
            'name' => 'Oromo',
            'code' => 'om',
            'iso_639_3' => 'orm',
        ],
        [
            'name' => 'Oriya',
            'code' => 'or',
            'iso_639_3' => 'ori',
        ],
        [
            'name' => 'Ossetian',
            'code' => 'os',
            'iso_639_3' => 'oss',
        ],
        [
            'name' => 'Punjabi',
            'code' => 'pa',
            'iso_639_3' => 'pan',
        ],
        [
            'name' => 'Pali',
            'code' => 'pi',
            'iso_639_3' => 'pli',
        ],
        [
            'name' => 'Persian',
            'code' => 'fa',
            'iso_639_3' => 'fas',
        ],
        [
            'name' => 'Polish',
            'code' => 'pl',
            'iso_639_3' => 'pol',
        ],
        [
            'name' => 'Pashto',
            'code' => 'ps',
            'iso_639_3' => 'pus',
        ],
        [
            'name' => 'Portuguese',
            'code' => 'pt',
            'iso_639_3' => 'por',
        ],
        [
            'name' => 'Quechua',
            'code' => 'qu',
            'iso_639_3' => 'que',
        ],
        [
            'name' => 'Romansh',
            'code' => 'rm',
            'iso_639_3' => 'roh',
        ],
        [
            'name' => 'Rundi',
            'code' => 'rn',
            'iso_639_3' => 'run',
        ],
        [
            'name' => 'Romanian',
            'code' => 'ro',
            'iso_639_3' => 'ron',
        ],
        [
            'name' => 'Russian',
            'code' => 'ru',
            'iso_639_3' => 'rus',
        ],
        [
            'name' => 'Sanskrit',
            'code' => 'sa',
            'iso_639_3' => 'san',
        ],
        [
            'name' => 'Sardinian',
            'code' => 'sc',
            'iso_639_3' => 'srd',
        ],
        [
            'name' => 'Sindhi',
            'code' => 'sd',
            'iso_639_3' => 'snd',
        ],
        [
            'name' => 'Northern Sami',
            'code' => 'se',
            'iso_639_3' => 'sme',
        ],
        [
            'name' => 'Samoan',
            'code' => 'sm',
            'iso_639_3' => 'smo',
        ],
        [
            'name' => 'Sango',
            'code' => 'sg',
            'iso_639_3' => 'sag',
        ],
        [
            'name' => 'Serbian',
            'code' => 'sr',
            'iso_639_3' => 'srp',
        ],
        [
            'name' => 'Gaelic',
            'code' => 'gd',
            'iso_639_3' => 'gla',
        ],
        [
            'name' => 'Shona',
            'code' => 'sn',
            'iso_639_3' => 'sna',
        ],
        [
            'name' => 'Sinhala',
            'code' => 'si',
            'iso_639_3' => 'sin',
        ],
        [
            'name' => 'Slovak',
            'code' => 'sk',
            'iso_639_3' => 'slk',
        ],
        [
            'name' => 'Slovenian',
            'code' => 'sl',
            'iso_639_3' => 'slv',
        ],
        [
            'name' => 'Somali',
            'code' => 'so',
            'iso_639_3' => 'som',
        ],
        [
            'name' => 'Southern Sotho',
            'code' => 'st',
            'iso_639_3' => 'sot',
        ],
        [
            'name' => 'Spanish',
            'code' => 'es',
            'iso_639_3' => 'spa',
        ],
        [
            'name' => 'Sundanese',
            'code' => 'su',
            'iso_639_3' => 'sun',
        ],
        [
            'name' => 'Swahili',
            'code' => 'sw',
            'iso_639_3' => 'swa',
        ],
        [
            'name' => 'Swati',
            'code' => 'ss',
            'iso_639_3' => 'ssw',
        ],
        [
            'name' => 'Swedish',
            'code' => 'sv',
            'iso_639_3' => 'swe',
        ],
        [
            'name' => 'Tamil',
            'code' => 'ta',
            'iso_639_3' => 'tam',
        ],
        [
            'name' => 'Telugu',
            'code' => 'te',
            'iso_639_3' => 'tel',
        ],
        [
            'name' => 'Tajik',
            'code' => 'tg',
            'iso_639_3' => 'tgk',
        ],
        [
            'name' => 'Thai',
            'code' => 'th',
            'iso_639_3' => 'tha',
        ],
        [
            'name' => 'Tigrinya',
            'code' => 'ti',
            'iso_639_3' => 'tir',
        ],
        [
            'name' => 'Tibetan',
            'code' => 'bo',
            'iso_639_3' => 'bod',
        ],
        [
            'name' => 'Turkmen',
            'code' => 'tk',
            'iso_639_3' => 'tuk',
        ],
        [
            'name' => 'Tagalog',
            'code' => 'tl',
            'iso_639_3' => 'tgl',
        ],
        [
            'name' => 'Tswana',
            'code' => 'tn',
            'iso_639_3' => 'tsn',
        ],
        [
            'name' => 'Tonga (Tonga Islands)',
            'code' => 'to',
            'iso_639_3' => 'ton',
        ],
        [
            'name' => 'Turkish',
            'code' => 'tr',
            'iso_639_3' => 'tur',
        ],
        [
            'name' => 'Tsonga',
            'code' => 'ts',
            'iso_639_3' => 'tso',
        ],
        [
            'name' => 'Tatar',
            'code' => 'tt',
            'iso_639_3' => 'tat',
        ],
        [
            'name' => 'Twi',
            'code' => 'tw',
            'iso_639_3' => 'twi',
        ],
        [
            'name' => 'Tahitian',
            'code' => 'ty',
            'iso_639_3' => 'tah',
        ],
        [
            'name' => 'Uighur',
            'code' => 'ug',
            'iso_639_3' => 'uig',
        ],
        [
            'name' => 'Ukrainian',
            'code' => 'uk',
            'iso_639_3' => 'ukr',
        ],
        [
            'name' => 'Urdu',
            'code' => 'ur',
            'iso_639_3' => 'urd',
        ],
        [
            'name' => 'Uzbek',
            'code' => 'uz',
            'iso_639_3' => 'uzb',
        ],
        [
            'name' => 'Venda',
            'code' => 've',
            'iso_639_3' => 'ven',
        ],
        [
            'name' => 'Vietnamese',
            'code' => 'vi',
            'iso_639_3' => 'vie',
        ],
        [
            'name' => 'Volapük',
            'code' => 'vo',
            'iso_639_3' => 'vol',
        ],
        [
            'name' => 'Walloon',
            'code' => 'wa',
            'iso_639_3' => 'wln',
        ],
        [
            'name' => 'Welsh',
            'code' => 'cy',
            'iso_639_3' => 'cym',
        ],
        [
            'name' => 'Wolof',
            'code' => 'wo',
            'iso_639_3' => 'wol',
        ],
        [
            'name' => 'Western Frisian',
            'code' => 'fy',
            'iso_639_3' => 'fry',
        ],
        [
            'name' => 'Xhosa',
            'code' => 'xh',
            'iso_639_3' => 'xho',
        ],
        [
            'name' => 'Yiddish',
            'code' => 'yi',
            'iso_639_3' => 'yid',
        ],
        [
            'name' => 'Yoruba',
            'code' => 'yo',
            'iso_639_3' => 'yor',
        ],
        [
            'name' => 'Zhuang, Chuang',
            'code' => 'za',
            'iso_639_3' => 'zha',
        ],
        [
            'name' => 'Zulu',
            'code' => 'zu',
            'iso_639_3' => 'zul',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->languages as $language) {
            Language::create($language);
        }
    }
}
