<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static WatchState Watching()
 * @method static WatchState Paused()
 * @method static WatchState Completed()
 */
final class WatchState extends Enum
{
    const int Watching = 0;
    const int Paused = 1;
    const int Completed = 2;
}
