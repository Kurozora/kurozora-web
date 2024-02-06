<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ScheduleKind Anime()
 * @method static ScheduleKind Manga()
 * @method static ScheduleKind Game()
 */
final class ScheduleKind extends Enum
{
    const int Anime = 0;
    const int Manga = 1;
    const int Game = 2;
}
