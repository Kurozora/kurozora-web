<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static CastRole Protagonist()
 * @method static CastRole Deuteragonist()
 * @method static CastRole Tritagonist()
 * @method static CastRole SupportingCharacter()
 * @method static CastRole Antagonist()
 * @method static CastRole Antihero()
 * @method static CastRole Archenemy()
 * @method static CastRole FocalCharacter()
 * @method static CastRole Foil()
 * @method static CastRole Narrator()
 * @method static CastRole TitleCharacter()
 */
final class CastRole extends Enum
{
    const Protagonist           = 0;
    const Deuteragonist         = 1;
    const Tritagonist           = 2;
    const SupportingCharacter   = 3;
    const Antagonist            = 4;
    const Antihero              = 5;
    const Archenemy             = 6;
    const FocalCharacter        = 7;
    const Foil                  = 8;
    const Narrator              = 9;
    const TitleCharacter        = 10;
}
