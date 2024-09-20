<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * The available countries.
     *
     * @var array $countries
     */
    protected array $countries = [
        [
            'name' => 'Andorra',
            'code' => 'ad',
            'iso_3166_3' => 'and',
        ],
        [
            'name' => 'United Arab Emirates',
            'code' => 'ae',
            'iso_3166_3' => 'are',
        ],
        [
            'name' => 'Afghanistan',
            'code' => 'af',
            'iso_3166_3' => 'afg',
        ],
        [
            'name' => 'Antigua and Barbuda',
            'code' => 'ag',
            'iso_3166_3' => 'atg',
        ],
        [
            'name' => 'Anguilla',
            'code' => 'ai',
            'iso_3166_3' => 'aia',
        ],
        [
            'name' => 'Albania',
            'code' => 'al',
            'iso_3166_3' => 'alb',
        ],
        [
            'name' => 'Armenia',
            'code' => 'am',
            'iso_3166_3' => 'arm',
        ],
        [
            'name' => 'Angola',
            'code' => 'ao',
            'iso_3166_3' => 'ago',
        ],
        [
            'name' => 'Antarctica',
            'code' => 'aq',
            'iso_3166_3' => 'ata',
        ],
        [
            'name' => 'Argentina',
            'code' => 'ar',
            'iso_3166_3' => 'arg',
        ],
        [
            'name' => 'American Samoa',
            'code' => 'as',
            'iso_3166_3' => 'asm',
        ],
        [
            'name' => 'Austria',
            'code' => 'at',
            'iso_3166_3' => 'aut',
        ],
        [
            'name' => 'Australia',
            'code' => 'au',
            'iso_3166_3' => 'aus',
        ],
        [
            'name' => 'Aruba',
            'code' => 'aw',
            'iso_3166_3' => 'abw',
        ],
        [
            'name' => 'Åland Islands',
            'code' => 'ax',
            'iso_3166_3' => 'ala',
        ],
        [
            'name' => 'Azerbaijan',
            'code' => 'az',
            'iso_3166_3' => 'aze',
        ],
        [
            'name' => 'Bosnia and Herzegovina',
            'code' => 'ba',
            'iso_3166_3' => 'bih',
        ],
        [
            'name' => 'Barbados',
            'code' => 'bb',
            'iso_3166_3' => 'brb',
        ],
        [
            'name' => 'Bangladesh',
            'code' => 'bd',
            'iso_3166_3' => 'bgd',
        ],
        [
            'name' => 'Belgium',
            'code' => 'be',
            'iso_3166_3' => 'bel',
        ],
        [
            'name' => 'Burkina Faso',
            'code' => 'bf',
            'iso_3166_3' => 'bfa',
        ],
        [
            'name' => 'Bulgaria',
            'code' => 'bg',
            'iso_3166_3' => 'bgr',
        ],
        [
            'name' => 'Bahrain',
            'code' => 'bh',
            'iso_3166_3' => 'bhr',
        ],
        [
            'name' => 'Burundi',
            'code' => 'bi',
            'iso_3166_3' => 'bdi',
        ],
        [
            'name' => 'Benin',
            'code' => 'bj',
            'iso_3166_3' => 'ben',
        ],
        [
            'name' => 'Saint Barthélemy',
            'code' => 'bl',
            'iso_3166_3' => 'blm',
        ],
        [
            'name' => 'Bermuda',
            'code' => 'bm',
            'iso_3166_3' => 'bmu',
        ],
        [
            'name' => 'Brunei Darussalam',
            'code' => 'bn',
            'iso_3166_3' => 'brn',
        ],
        [
            'name' => 'Bolivia',
            'code' => 'bo',
            'iso_3166_3' => 'bol',
        ],
        [
            'name' => 'Bonaire, Sint Eustatius and Saba',
            'code' => 'bq',
            'iso_3166_3' => 'bes',
        ],
        [
            'name' => 'Brazil',
            'code' => 'br',
            'iso_3166_3' => 'bra',
        ],
        [
            'name' => 'Bahamas',
            'code' => 'bs',
            'iso_3166_3' => 'bhs',
        ],
        [
            'name' => 'Bhutan',
            'code' => 'bt',
            'iso_3166_3' => 'btn',
        ],
        [
            'name' => 'Bouvet Island',
            'code' => 'bv',
            'iso_3166_3' => 'bvt',
        ],
        [
            'name' => 'Botswana',
            'code' => 'bw',
            'iso_3166_3' => 'bwa',
        ],
        [
            'name' => 'Belarus',
            'code' => 'by',
            'iso_3166_3' => 'blr',
        ],
        [
            'name' => 'Belize',
            'code' => 'bz',
            'iso_3166_3' => 'blz',
        ],
        [
            'name' => 'Canada',
            'code' => 'ca',
            'iso_3166_3' => 'can',
        ],
        [
            'name' => 'Cocos (Keeling) Islands',
            'code' => 'cc',
            'iso_3166_3' => 'cck',
        ],
        [
            'name' => 'Congo, Democratic Republic of the',
            'code' => 'cd',
            'iso_3166_3' => 'cod',
        ],
        [
            'name' => 'Central African Republic',
            'code' => 'cf',
            'iso_3166_3' => 'caf',
        ],
        [
            'name' => 'Congo',
            'code' => 'cg',
            'iso_3166_3' => 'cog',
        ],
        [
            'name' => 'Switzerland',
            'code' => 'ch',
            'iso_3166_3' => 'che',
        ],
        [
            'name' => "Côte d'Ivoire",
            'code' => 'ci',
            'iso_3166_3' => 'civ',
        ],
        [
            'name' => 'Cook Islands',
            'code' => 'ck',
            'iso_3166_3' => 'cok',
        ],
        [
            'name' => 'Chile',
            'code' => 'cl',
            'iso_3166_3' => 'chl',
        ],
        [
            'name' => 'Cameroon',
            'code' => 'cm',
            'iso_3166_3' => 'cmr',
        ],
        [
            'name' => 'China',
            'code' => 'cn',
            'iso_3166_3' => 'chn',
        ],
        [
            'name' => 'Colombia',
            'code' => 'co',
            'iso_3166_3' => 'col',
        ],
        [
            'name' => 'Costa Rica',
            'code' => 'cr',
            'iso_3166_3' => 'cri',
        ],
        [
            'name' => 'Cuba',
            'code' => 'cu',
            'iso_3166_3' => 'cub',
        ],
        [
            'name' => 'Cabo Verde',
            'code' => 'cv',
            'iso_3166_3' => 'cpv',
        ],
        [
            'name' => 'Curaçao',
            'code' => 'cw',
            'iso_3166_3' => 'cuw',
        ],
        [
            'name' => 'Christmas Island',
            'code' => 'cx',
            'iso_3166_3' => 'cxr',
        ],
        [
            'name' => 'Cyprus',
            'code' => 'cy',
            'iso_3166_3' => 'cyp',
        ],
        [
            'name' => 'Czechia',
            'code' => 'cz',
            'iso_3166_3' => 'cze',
        ],
        [
            'name' => 'Germany',
            'code' => 'de',
            'iso_3166_3' => 'deu',
        ],
        [
            'name' => 'Djibouti',
            'code' => 'dj',
            'iso_3166_3' => 'dji',
        ],
        [
            'name' => 'Denmark',
            'code' => 'dk',
            'iso_3166_3' => 'dnk',
        ],
        [
            'name' => 'Dominica',
            'code' => 'dm',
            'iso_3166_3' => 'dma',
        ],
        [
            'name' => 'Dominican Republic',
            'code' => 'do',
            'iso_3166_3' => 'dom',
        ],
        [
            'name' => 'Algeria',
            'code' => 'dz',
            'iso_3166_3' => 'dza',
        ],
        [
            'name' => 'Ecuador',
            'code' => 'ec',
            'iso_3166_3' => 'ecu',
        ],
        [
            'name' => 'Estonia',
            'code' => 'ee',
            'iso_3166_3' => 'est',
        ],
        [
            'name' => 'Egypt',
            'code' => 'eg',
            'iso_3166_3' => 'egy',
        ],
        [
            'name' => 'Western Sahara',
            'code' => 'eh',
            'iso_3166_3' => 'esh',
        ],
        [
            'name' => 'Eritrea',
            'code' => 'er',
            'iso_3166_3' => 'eri',
        ],
        [
            'name' => 'Spain',
            'code' => 'es',
            'iso_3166_3' => 'esp',
        ],
        [
            'name' => 'Ethiopia',
            'code' => 'et',
            'iso_3166_3' => 'eth',
        ],
        [
            'name' => 'Finland',
            'code' => 'fi',
            'iso_3166_3' => 'fin',
        ],
        [
            'name' => 'Fiji',
            'code' => 'fj',
            'iso_3166_3' => 'fji',
        ],
        [
            'name' => 'Falkland Islands (Malvinas)',
            'code' => 'fk',
            'iso_3166_3' => 'flk',
        ],
        [
            'name' => 'Micronesia',
            'code' => 'fm',
            'iso_3166_3' => 'fsm',
        ],
        [
            'name' => 'Faroe Islands',
            'code' => 'fo',
            'iso_3166_3' => 'fro',
        ],
        [
            'name' => 'France',
            'code' => 'fr',
            'iso_3166_3' => 'fra',
        ],
        [
            'name' => 'Gabon',
            'code' => 'ga',
            'iso_3166_3' => 'gab',
        ],
        [
            'name' => 'United Kingdom',
            'code' => 'gb',
            'iso_3166_3' => 'gbr',
        ],
        [
            'name' => 'Grenada',
            'code' => 'gd',
            'iso_3166_3' => 'grd',
        ],
        [
            'name' => 'Georgia',
            'code' => 'ge',
            'iso_3166_3' => 'geo',
        ],
        [
            'name' => 'French Guiana',
            'code' => 'gf',
            'iso_3166_3' => 'guf',
        ],
        [
            'name' => 'Guernsey',
            'code' => 'gg',
            'iso_3166_3' => 'ggy',
        ],
        [
            'name' => 'Ghana',
            'code' => 'gh',
            'iso_3166_3' => 'gha',
        ],
        [
            'name' => 'Gibraltar',
            'code' => 'gi',
            'iso_3166_3' => 'gib',
        ],
        [
            'name' => 'Greenland',
            'code' => 'gl',
            'iso_3166_3' => 'grl',
        ],
        [
            'name' => 'Gambia',
            'code' => 'gm',
            'iso_3166_3' => 'gmb',
        ],
        [
            'name' => 'Guinea',
            'code' => 'gn',
            'iso_3166_3' => 'gin',
        ],
        [
            'name' => 'Guadeloupe',
            'code' => 'gp',
            'iso_3166_3' => 'glp',
        ],
        [
            'name' => 'Equatorial Guinea',
            'code' => 'gq',
            'iso_3166_3' => 'gnq',
        ],
        [
            'name' => 'Greece',
            'code' => 'gr',
            'iso_3166_3' => 'grc',
        ],
        [
            'name' => 'South Georgia and the South Sandwich Islands',
            'code' => 'gs',
            'iso_3166_3' => 'sgs',
        ],
        [
            'name' => 'Guatemala',
            'code' => 'gt',
            'iso_3166_3' => 'gtm',
        ],
        [
            'name' => 'Guam',
            'code' => 'gu',
            'iso_3166_3' => 'gum',
        ],
        [
            'name' => 'Guinea-Bissau',
            'code' => 'gw',
            'iso_3166_3' => 'gnb',
        ],
        [
            'name' => 'Guyana',
            'code' => 'gy',
            'iso_3166_3' => 'guy',
        ],
        [
            'name' => 'Hong Kong',
            'code' => 'hk',
            'iso_3166_3' => 'hkg',
        ],
        [
            'name' => 'Heard Island and McDonald Islands',
            'code' => 'hm',
            'iso_3166_3' => 'hmd',
        ],
        [
            'name' => 'Honduras',
            'code' => 'hn',
            'iso_3166_3' => 'hnd',
        ],
        [
            'name' => 'Croatia',
            'code' => 'hr',
            'iso_3166_3' => 'hrv',
        ],
        [
            'name' => 'Haiti',
            'code' => 'ht',
            'iso_3166_3' => 'hti',
        ],
        [
            'name' => 'Hungary',
            'code' => 'hu',
            'iso_3166_3' => 'hun',
        ],
        [
            'name' => 'Indonesia',
            'code' => 'id',
            'iso_3166_3' => 'idn',
        ],
        [
            'name' => 'Ireland',
            'code' => 'ie',
            'iso_3166_3' => 'irl',
        ],
        [
            'name' => 'Israel',
            'code' => 'il',
            'iso_3166_3' => 'isr',
        ],
        [
            'name' => 'Isle of Man',
            'code' => 'im',
            'iso_3166_3' => 'imn',
        ],
        [
            'name' => 'India',
            'code' => 'in',
            'iso_3166_3' => 'ind',
        ],
        [
            'name' => 'British Indian Ocean Territory',
            'code' => 'io',
            'iso_3166_3' => 'iot',
        ],
        [
            'name' => 'Iraq',
            'code' => 'iq',
            'iso_3166_3' => 'irq',
        ],
        [
            'name' => 'Iran',
            'code' => 'ir',
            'iso_3166_3' => 'irn',
        ],
        [
            'name' => 'Iceland',
            'code' => 'is',
            'iso_3166_3' => 'isl',
        ],
        [
            'name' => 'Italy',
            'code' => 'it',
            'iso_3166_3' => 'ita',
        ],
        [
            'name' => 'Jersey',
            'code' => 'je',
            'iso_3166_3' => 'jey',
        ],
        [
            'name' => 'Jamaica',
            'code' => 'jm',
            'iso_3166_3' => 'jam',
        ],
        [
            'name' => 'Jordan',
            'code' => 'jo',
            'iso_3166_3' => 'jor',
        ],
        [
            'name' => 'Japan',
            'code' => 'jp',
            'iso_3166_3' => 'jpn',
        ],
        [
            'name' => 'Kenya',
            'code' => 'ke',
            'iso_3166_3' => 'ken',
        ],
        [
            'name' => 'Kyrgyzstan',
            'code' => 'kg',
            'iso_3166_3' => 'kgz',
        ],
        [
            'name' => 'Cambodia',
            'code' => 'kh',
            'iso_3166_3' => 'khm',
        ],
        [
            'name' => 'Kiribati',
            'code' => 'ki',
            'iso_3166_3' => 'kir',
        ],
        [
            'name' => 'Comoros',
            'code' => 'km',
            'iso_3166_3' => 'com',
        ],
        [
            'name' => 'Saint Kitts and Nevis',
            'code' => 'kn',
            'iso_3166_3' => 'kna',
        ],
        [
            'name' => 'North Korea',
            'code' => 'kp',
            'iso_3166_3' => 'prk',
        ],
        [
            'name' => 'South Korea',
            'code' => 'kr',
            'iso_3166_3' => 'kor',
        ],
        [
            'name' => 'Kuwait',
            'code' => 'kw',
            'iso_3166_3' => 'kwt',
        ],
        [
            'name' => 'Cayman Islands',
            'code' => 'ky',
            'iso_3166_3' => 'cym',
        ],
        [
            'name' => 'Kazakhstan',
            'code' => 'kz',
            'iso_3166_3' => 'kaz',
        ],
        [
            'name' => "Lao People's Democratic Republic",
            'code' => 'la',
            'iso_3166_3' => 'lao',
        ],
        [
            'name' => 'Lebanon',
            'code' => 'lb',
            'iso_3166_3' => 'lbn',
        ],
        [
            'name' => 'Saint Lucia',
            'code' => 'lc',
            'iso_3166_3' => 'lca',
        ],
        [
            'name' => 'Liechtenstein',
            'code' => 'li',
            'iso_3166_3' => 'lie',
        ],
        [
            'name' => 'Sri Lanka',
            'code' => 'lk',
            'iso_3166_3' => 'lka',
        ],
        [
            'name' => 'Liberia',
            'code' => 'lr',
            'iso_3166_3' => 'lbr',
        ],
        [
            'name' => 'Lesotho',
            'code' => 'ls',
            'iso_3166_3' => 'lso',
        ],
        [
            'name' => 'Lithuania',
            'code' => 'lt',
            'iso_3166_3' => 'ltu',
        ],
        [
            'name' => 'Luxembourg',
            'code' => 'lu',
            'iso_3166_3' => 'lux',
        ],
        [
            'name' => 'Latvia',
            'code' => 'lv',
            'iso_3166_3' => 'lva',
        ],
        [
            'name' => 'Libya',
            'code' => 'ly',
            'iso_3166_3' => 'lby',
        ],
        [
            'name' => 'Morocco',
            'code' => 'ma',
            'iso_3166_3' => 'mar',
        ],
        [
            'name' => 'Monaco',
            'code' => 'mc',
            'iso_3166_3' => 'mco',
        ],
        [
            'name' => 'Moldova',
            'code' => 'md',
            'iso_3166_3' => 'mda',
        ],
        [
            'name' => 'Montenegro',
            'code' => 'me',
            'iso_3166_3' => 'mne',
        ],
        [
            'name' => 'Saint Martin (French part)',
            'code' => 'mf',
            'iso_3166_3' => 'maf',
        ],
        [
            'name' => 'Madagascar',
            'code' => 'mg',
            'iso_3166_3' => 'mdg',
        ],
        [
            'name' => 'Marshall Islands',
            'code' => 'mh',
            'iso_3166_3' => 'mhl',
        ],
        [
            'name' => 'North Macedonia',
            'code' => 'mk',
            'iso_3166_3' => 'mkd',
        ],
        [
            'name' => 'Mali',
            'code' => 'ml',
            'iso_3166_3' => 'mli',
        ],
        [
            'name' => 'Myanmar',
            'code' => 'mm',
            'iso_3166_3' => 'mmr',
        ],
        [
            'name' => 'Mongolia',
            'code' => 'mn',
            'iso_3166_3' => 'mng',
        ],
        [
            'name' => 'Macao',
            'code' => 'mo',
            'iso_3166_3' => 'mac',
        ],
        [
            'name' => 'Northern Mariana Islands',
            'code' => 'mp',
            'iso_3166_3' => 'mnp',
        ],
        [
            'name' => 'Martinique',
            'code' => 'mq',
            'iso_3166_3' => 'mtq',
        ],
        [
            'name' => 'Mauritania',
            'code' => 'mr',
            'iso_3166_3' => 'mrt',
        ],
        [
            'name' => 'Montserrat',
            'code' => 'ms',
            'iso_3166_3' => 'msr',
        ],
        [
            'name' => 'Malta',
            'code' => 'mt',
            'iso_3166_3' => 'mlt',
        ],
        [
            'name' => 'Mauritius',
            'code' => 'mu',
            'iso_3166_3' => 'mus',
        ],
        [
            'name' => 'Maldives',
            'code' => 'mv',
            'iso_3166_3' => 'mdv',
        ],
        [
            'name' => 'Malawi',
            'code' => 'mw',
            'iso_3166_3' => 'mwi',
        ],
        [
            'name' => 'Mexico',
            'code' => 'mx',
            'iso_3166_3' => 'mex',
        ],
        [
            'name' => 'Malaysia',
            'code' => 'my',
            'iso_3166_3' => 'mys',
        ],
        [
            'name' => 'Mozambique',
            'code' => 'mz',
            'iso_3166_3' => 'moz',
        ],
        [
            'name' => 'Namibia',
            'code' => 'na',
            'iso_3166_3' => 'nam',
        ],
        [
            'name' => 'New Caledonia',
            'code' => 'nc',
            'iso_3166_3' => 'ncl',
        ],
        [
            'name' => 'Niger',
            'code' => 'ne',
            'iso_3166_3' => 'ner',
        ],
        [
            'name' => 'Norfolk Island',
            'code' => 'nf',
            'iso_3166_3' => 'nfk',
        ],
        [
            'name' => 'Nigeria',
            'code' => 'ng',
            'iso_3166_3' => 'nga',
        ],
        [
            'name' => 'Nicaragua',
            'code' => 'ni',
            'iso_3166_3' => 'nic',
        ],
        [
            'name' => 'The Netherlands',
            'code' => 'nl',
            'iso_3166_3' => 'nld',
        ],
        [
            'name' => 'Norway',
            'code' => 'no',
            'iso_3166_3' => 'nor',
        ],
        [
            'name' => 'Nepal',
            'code' => 'np',
            'iso_3166_3' => 'npl',
        ],
        [
            'name' => 'Nauru',
            'code' => 'nr',
            'iso_3166_3' => 'nru',
        ],
        [
            'name' => 'Niue',
            'code' => 'nu',
            'iso_3166_3' => 'niu',
        ],
        [
            'name' => 'New Zealand',
            'code' => 'nz',
            'iso_3166_3' => 'nzl',
        ],
        [
            'name' => 'Oman',
            'code' => 'om',
            'iso_3166_3' => 'omn',
        ],
        [
            'name' => 'Panama',
            'code' => 'pa',
            'iso_3166_3' => 'pan',
        ],
        [
            'name' => 'Peru',
            'code' => 'pe',
            'iso_3166_3' => 'per',
        ],
        [
            'name' => 'French Polynesia',
            'code' => 'pf',
            'iso_3166_3' => 'pyf',
        ],
        [
            'name' => 'Papua New Guinea',
            'code' => 'pg',
            'iso_3166_3' => 'png',
        ],
        [
            'name' => 'Philippines',
            'code' => 'ph',
            'iso_3166_3' => 'phl',
        ],
        [
            'name' => 'Pakistan',
            'code' => 'pk',
            'iso_3166_3' => 'pak',
        ],
        [
            'name' => 'Poland',
            'code' => 'pl',
            'iso_3166_3' => 'pol',
        ],
        [
            'name' => 'Saint Pierre and Miquelon',
            'code' => 'pm',
            'iso_3166_3' => 'spm',
        ],
        [
            'name' => 'Pitcairn',
            'code' => 'pn',
            'iso_3166_3' => 'pcn',
        ],
        [
            'name' => 'Puerto Rico',
            'code' => 'pr',
            'iso_3166_3' => 'pri',
        ],
        [
            'name' => 'Palestine',
            'code' => 'ps',
            'iso_3166_3' => 'pse',
        ],
        [
            'name' => 'Portugal',
            'code' => 'pt',
            'iso_3166_3' => 'prt',
        ],
        [
            'name' => 'Palau',
            'code' => 'pw',
            'iso_3166_3' => 'plw',
        ],
        [
            'name' => 'Paraguay',
            'code' => 'py',
            'iso_3166_3' => 'pry',
        ],
        [
            'name' => 'Qatar',
            'code' => 'qa',
            'iso_3166_3' => 'qat',
        ],
        [
            'name' => 'Réunion',
            'code' => 're',
            'iso_3166_3' => 'reu',
        ],
        [
            'name' => 'Romania',
            'code' => 'ro',
            'iso_3166_3' => 'rou',
        ],
        [
            'name' => 'Serbia',
            'code' => 'rs',
            'iso_3166_3' => 'srb',
        ],
        [
            'name' => 'Russian Federation',
            'code' => 'ru',
            'iso_3166_3' => 'rus',
        ],
        [
            'name' => 'Rwanda',
            'code' => 'rw',
            'iso_3166_3' => 'rwa',
        ],
        [
            'name' => 'Saudi Arabia',
            'code' => 'sa',
            'iso_3166_3' => 'sau',
        ],
        [
            'name' => 'Solomon Islands',
            'code' => 'sb',
            'iso_3166_3' => 'slb',
        ],
        [
            'name' => 'Seychelles',
            'code' => 'sc',
            'iso_3166_3' => 'syc',
        ],
        [
            'name' => 'Sudan',
            'code' => 'sd',
            'iso_3166_3' => 'sdn',
        ],
        [
            'name' => 'Sweden',
            'code' => 'se',
            'iso_3166_3' => 'swe',
        ],
        [
            'name' => 'Singapore',
            'code' => 'sg',
            'iso_3166_3' => 'sgp',
        ],
        [
            'name' => 'Saint Helena',
            'code' => 'sh',
            'iso_3166_3' => 'shn',
        ],
        [
            'name' => 'Slovenia',
            'code' => 'si',
            'iso_3166_3' => 'svn',
        ],
        [
            'name' => 'Svalbard and Jan Mayen',
            'code' => 'sj',
            'iso_3166_3' => 'sjm',
        ],
        [
            'name' => 'Slovakia',
            'code' => 'sk',
            'iso_3166_3' => 'svk',
        ],
        [
            'name' => 'Sierra Leone',
            'code' => 'sl',
            'iso_3166_3' => 'sle',
        ],
        [
            'name' => 'San Marino',
            'code' => 'sm',
            'iso_3166_3' => 'smr',
        ],
        [
            'name' => 'Senegal',
            'code' => 'sn',
            'iso_3166_3' => 'sen',
        ],
        [
            'name' => 'Somalia',
            'code' => 'so',
            'iso_3166_3' => 'som',
        ],
        [
            'name' => 'Suriname',
            'code' => 'sr',
            'iso_3166_3' => 'sur',
        ],
        [
            'name' => 'South Sudan',
            'code' => 'ss',
            'iso_3166_3' => 'ssd',
        ],
        [
            'name' => 'Sao Tome and Principe',
            'code' => 'st',
            'iso_3166_3' => 'stp',
        ],
        [
            'name' => 'El Salvador',
            'code' => 'sv',
            'iso_3166_3' => 'slv',
        ],
        [
            'name' => 'Sint Maarten (Dutch part)',
            'code' => 'sx',
            'iso_3166_3' => 'sxm',
        ],
        [
            'name' => 'Syrian Arab Republic',
            'code' => 'sy',
            'iso_3166_3' => 'syr',
        ],
        [
            'name' => 'Eswatini',
            'code' => 'sz',
            'iso_3166_3' => 'swz',
        ],
        [
            'name' => 'Turks and Caicos Islands',
            'code' => 'tc',
            'iso_3166_3' => 'tca',
        ],
        [
            'name' => 'Chad',
            'code' => 'td',
            'iso_3166_3' => 'tcd',
        ],
        [
            'name' => 'French Southern Territories',
            'code' => 'tf',
            'iso_3166_3' => 'atf',
        ],
        [
            'name' => 'Togo',
            'code' => 'tg',
            'iso_3166_3' => 'tgo',
        ],
        [
            'name' => 'Thailand',
            'code' => 'th',
            'iso_3166_3' => 'tha',
        ],
        [
            'name' => 'Tajikistan',
            'code' => 'tj',
            'iso_3166_3' => 'tjk',
        ],
        [
            'name' => 'Tokelau',
            'code' => 'tk',
            'iso_3166_3' => 'tkl',
        ],
        [
            'name' => 'Timor-Leste',
            'code' => 'tl',
            'iso_3166_3' => 'tls',
        ],
        [
            'name' => 'Turkmenistan',
            'code' => 'tm',
            'iso_3166_3' => 'tkm',
        ],
        [
            'name' => 'Tunisia',
            'code' => 'tn',
            'iso_3166_3' => 'tun',
        ],
        [
            'name' => 'Tonga',
            'code' => 'to',
            'iso_3166_3' => 'ton',
        ],
        [
            'name' => 'Türkiye',
            'code' => 'tr',
            'iso_3166_3' => 'tur',
        ],
        [
            'name' => 'Trinidad and Tobago',
            'code' => 'tt',
            'iso_3166_3' => 'tto',
        ],
        [
            'name' => 'Tuvalu',
            'code' => 'tv',
            'iso_3166_3' => 'tuv',
        ],
        [
            'name' => 'Taiwan',
            'code' => 'tw',
            'iso_3166_3' => 'twn',
        ],
        [
            'name' => 'Tanzania',
            'code' => 'tz',
            'iso_3166_3' => 'tza',
        ],
        [
            'name' => 'Ukraine',
            'code' => 'ua',
            'iso_3166_3' => 'ukr',
        ],
        [
            'name' => 'Uganda',
            'code' => 'ug',
            'iso_3166_3' => 'uga',
        ],
        [
            'name' => 'United States Minor Outlying Islands',
            'code' => 'um',
            'iso_3166_3' => 'umi',
        ],
        [
            'name' => 'United States',
            'code' => 'us',
            'iso_3166_3' => 'usa',
        ],
        [
            'name' => 'Uruguay',
            'code' => 'uy',
            'iso_3166_3' => 'ury',
        ],
        [
            'name' => 'Uzbekistan',
            'code' => 'uz',
            'iso_3166_3' => 'uzb',
        ],
        [
            'name' => 'Holy See',
            'code' => 'va',
            'iso_3166_3' => 'vat',
        ],
        [
            'name' => 'Saint Vincent and the Grenadines',
            'code' => 'vc',
            'iso_3166_3' => 'vct',
        ],
        [
            'name' => 'Venezuela',
            'code' => 've',
            'iso_3166_3' => 'ven',
        ],
        [
            'name' => 'Virgin Islands (British)',
            'code' => 'vg',
            'iso_3166_3' => 'vgb',
        ],
        [
            'name' => 'Virgin Islands (U.S.)',
            'code' => 'vi',
            'iso_3166_3' => 'vir',
        ],
        [
            'name' => 'Viet Nam',
            'code' => 'vn',
            'iso_3166_3' => 'vnm',
        ],
        [
            'name' => 'Vanuatu',
            'code' => 'vu',
            'iso_3166_3' => 'vut',
        ],
        [
            'name' => 'Wallis and Futuna',
            'code' => 'wf',
            'iso_3166_3' => 'wlf',
        ],
        [
            'name' => 'Samoa',
            'code' => 'ws',
            'iso_3166_3' => 'wsm',
        ],
        [
            'name' => 'Yemen',
            'code' => 'ye',
            'iso_3166_3' => 'yem',
        ],
        [
            'name' => 'Mayotte',
            'code' => 'yt',
            'iso_3166_3' => 'myt',
        ],
        [
            'name' => 'South Africa',
            'code' => 'za',
            'iso_3166_3' => 'zaf',
        ],
        [
            'name' => 'Zambia',
            'code' => 'zm',
            'iso_3166_3' => 'zmb',
        ],
        [
            'name' => 'Zimbabwe',
            'code' => 'zw',
            'iso_3166_3' => 'zwe',
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->countries as $language) {
            Country::create($language);
        }
    }
}
