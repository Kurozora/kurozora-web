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
    const string Anime = 'anime';
    const string Characters = 'characters';
    const string Episodes = 'episodes';
    const string Games = 'games';
    const string Manga = 'manga';
    const string People = 'people';
    const string Songs = 'songs';
    const string Studios = 'studios';
}
