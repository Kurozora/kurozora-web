<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static TrailerSort JustAdded()
 * @method static TrailerSort MostPopular()
 * @method static TrailerSort Trending()
 * @method static TrailerSort MostAnticipated()
 */
final class TrailerSort extends Enum
{
    const int JustAdded       = 0;
    const int MostPopular     = 1;
    const int Trending        = 2;
    const int MostAnticipated = 3;
}
