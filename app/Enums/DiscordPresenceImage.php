<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static DiscordPresenceImage Poster()
 * @method static DiscordPresenceImage EpisodeBanner()
 * @method static DiscordPresenceImage Website()
 * @method static DiscordPresenceImage Kurozora()
 */
final class DiscordPresenceImage extends Enum
{
    const int Poster = 0;
    const int EpisodeBanner = 1;
    const int Website = 2;
    const int Kurozora = 3;
}
