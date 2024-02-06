<?php declare(strict_types=1);

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
    const int Unknown   = 0;
    const int Alive     = 1;
    const int Deceased  = 2;
    const int Missing   = 3;
}
