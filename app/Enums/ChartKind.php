<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ChartKind Anime()
 * @method static ChartKind Charcters()
 * @method static ChartKind Episodes()
 * @method static ChartKind Games()
 * @method static ChartKind Manga()
 * @method static ChartKind People()
 * @method static ChartKind Songs()
 * @method static ChartKind Studios()
 */
final class ChartKind extends Enum
{
    const Anime = 'anime';
    const Characters = 'characters';
    const Episodes = 'episodes';
    const Games = 'games';
    const Manga = 'manga';
    const People = 'people';
    const Songs = 'songs';
    const Studios = 'studios';
}
