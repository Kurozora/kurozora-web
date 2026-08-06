<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static DiscordActivityName Title()
 * @method static DiscordActivityName Website()
 * @method static DiscordActivityName Kind()
 * @method static DiscordActivityName Kurozora()
 */
final class DiscordActivityName extends Enum
{
    const int Title = 0;
    const int Website = 1;
    const int Kind = 2;
    const int Kurozora = 3;
}
