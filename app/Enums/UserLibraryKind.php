<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static UserLibraryStatus Anime()
 * @method static UserLibraryStatus Manga()
 * @method static UserLibraryStatus Game()
 */
final class UserLibraryKind extends Enum
{
    const Anime = 0;
    const Manga = 1;
    const Game = 2;
}
