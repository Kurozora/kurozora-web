<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static BrowseSeasonKind Anime()
 * @method static BrowseSeasonKind Manga()
 * @method static BrowseSeasonKind Game()
 */
final class BrowseSeasonKind extends Enum
{
    const int Anime =   0;
    const int Manga =   1;
    const int Game =    2;
}
