<?php declare(strict_types=1);

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
    const int Anime =   0;
    const int Manga =   1;
    const int Game =    2;
    const int Act =     3;
    const int Record =  4;
}
