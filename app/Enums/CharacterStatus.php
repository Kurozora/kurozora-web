<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static CharacterStatus Unknown()
 * @method static CharacterStatus Alive()
 * @method static CharacterStatus Deceased()
 * @method static CharacterStatus Missing()
 */
final class CharacterStatus extends Enum
{
    const Unknown   = 0;
    const Alive     = 1;
    const Deceased  = 2;
    const Missing   = 3;
}
