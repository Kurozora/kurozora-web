<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static AnimeImageType Poster()
 * @method static AnimeImageType Banner()
 */
final class AnimeImageType extends Enum
{
    const Poster =   0;
    const Banner =   1;
}
