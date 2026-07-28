<?php declare(strict_types=1);

namespace App\Enums\Minigames\Kotodama;

use BenSampo\Enum\Enum;

/**
 * @method static GameStatus InProgress()
 * @method static GameStatus Won()
 * @method static GameStatus Lost()
 * @method static GameStatus Abandoned()
 */
final class GameStatus extends Enum
{
    const int InProgress = 0;
    const int Won        = 1;
    const int Lost       = 2;
    const int Abandoned  = 3;
}
