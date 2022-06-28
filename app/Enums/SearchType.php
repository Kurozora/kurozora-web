<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static SearchType Characters()
 * @method static SearchType Games()
 * @method static SearchType Literature()
 * @method static SearchType People()
 * @method static SearchType Shows()
 * @method static SearchType Songs()
 * @method static SearchType Studios()
 * @method static SearchType Users()
 */
final class SearchType extends Enum
{
    const Characters = 'characters';
    const Games = 'games';
    const Literature = 'literature';
    const People = 'people';
    const Shows = 'shows';
    const Songs = 'songs';
    const Studios = 'studios';
    const Users = 'users';
}
