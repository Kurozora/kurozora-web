<?php declare(strict_types=1);

namespace App\Enums\Minigames\Kotodama;

use BenSampo\Enum\Enum;

/**
 * @method static Difficulty Easy()
 * @method static Difficulty Normal()
 * @method static Difficulty Hard()
 */
final class Difficulty extends Enum
{
    const int Easy   = 1;
    const int Normal = 2;
    const int Hard   = 3;
}
