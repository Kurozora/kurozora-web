<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static UserLibraryKind Anime()
 * @method static UserLibraryKind Manga()
 * @method static UserLibraryKind Game()
 */
final class UserLibraryKind extends Enum
{
    const int Anime = 0;
    const int Manga = 1;
    const int Game = 2;
}
