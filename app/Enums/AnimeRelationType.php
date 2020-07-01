<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static AnimeRelationType Sequel()
 * @method static AnimeRelationType Prequel()
 * @method static AnimeRelationType AlternativeSettings()
 * @method static AnimeRelationType AlternativeVersion()
 * @method static AnimeRelationType SideStory()
 * @method static AnimeRelationType Summary()
 * @method static AnimeRelationType FullStory()
 * @method static AnimeRelationType ParentStory()
 * @method static AnimeRelationType SpinOff()
 * @method static AnimeRelationType Adaptation()
 * @method static AnimeRelationType Character()
 * @method static AnimeRelationType Other()
 */
final class AnimeRelationType extends Enum
{
    const Sequel                = 0;
    const Prequel               = 1;
    const AlternativeSettings   = 2;
    const AlternativeVersion    = 3;
    const SideStory             = 4;
    const Summary               = 5;
    const FullStory             = 6;
    const ParentStory           = 7;
    const SpinOff               = 8;
    const Adaptation            = 9;
    const Character             = 10;
    const Other                 = 11;
}
