<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Opening()
 * @method static static Ending()
 * @method static static Background()
 */
final class SongType extends Enum
{
    const Opening       =   1;
    const Ending        =   2;
    const Background    =   3;
}
