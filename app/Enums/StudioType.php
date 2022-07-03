<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static StudioType Anime()
 * @method static StudioType Manga()
 * @method static StudioType Game()
 * @method static StudioType Act()
 * @method static StudioType Record()
 */
final class StudioType extends Enum
{
    const Anime =   0;
    const Manga =   1;
    const Game =    2;
    const Act =     3;
    const Record =  4;
}
