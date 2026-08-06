<?php declare(strict_types=1);

namespace App\Enums\Minigames\Kotodama;

use BenSampo\Enum\Enum;

/**
 * @method static GameMode Daily()
 * @method static GameMode Unlimited()
 * @method static GameMode Versus()
 * @method static GameMode Archive()
 */
final class GameMode extends Enum
{
    const int Daily     = 1;
    const int Unlimited = 2;
    const int Versus    = 3;
    const int Archive   = 4;
}
