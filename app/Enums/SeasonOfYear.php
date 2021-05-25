<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static SeasonOfYear Winter()
 * @method static SeasonOfYear Spring()
 * @method static SeasonOfYear Summer()
 * @method static SeasonOfYear Fall()
 */
final class SeasonOfYear extends Enum
{
    const Winter    = 0;
    const Spring    = 1;
    const Summer    = 2;
    const Fall      = 3;
}
