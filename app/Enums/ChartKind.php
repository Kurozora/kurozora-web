<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ChartKind Anime()
 * @method static ChartKind Episodes()
 * @method static ChartKind Games()
 * @method static ChartKind Manga()
 * @method static ChartKind Songs()
 */
final class ChartKind extends Enum
{
    const Anime = 'anime';
    const Episodes = 'episodes';
    const Games = 'games';
    const Manga = 'manga';
    const Songs = 'songs';
}
