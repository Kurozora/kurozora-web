<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static GameMode SinglePlayer()
 * @method static GameMode Multiplayer()
 * @method static GameMode Cooperative()
 * @method static GameMode SplitScreen()
 * @method static GameMode MMO()
 * @method static GameMode BattleRoyale()
 */
final class GameMode extends Enum
{
    const int SinglePlayer =    1;
    const int Multiplayer =     2;
    const int Cooperative =     3;
    const int SplitScreen =     4;
    const int MMO =             5;
    const int BattleRoyale =    6;
}
