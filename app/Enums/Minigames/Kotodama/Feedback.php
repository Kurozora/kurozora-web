<?php declare(strict_types=1);

namespace App\Enums\Minigames\Kotodama;

use BenSampo\Enum\Enum;

/**
 * @method static Feedback Hit()
 * @method static Feedback Present()
 * @method static Feedback Miss()
 */
final class Feedback extends Enum
{
    const string Hit     = 'H';
    const string Present = 'P';
    const string Miss    = 'M';
}
