<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ChartKind Anime()
 * @method static ChartKind Characters()
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

    /**
     * The library kind the given chart lists.
     *
     * @param string $kind
     *
     * @return int|null
     */
    public static function libraryKind(string $kind): ?int
    {
        return match ($kind) {
            self::Anime => UserLibraryKind::Anime,
            self::Manga => UserLibraryKind::Manga,
            self::Games => UserLibraryKind::Game,
            default => null,
        };
    }

    /**
     * The lockup the given chart renders.
     *
     * @param string $kind
     *
     * @return string
     */
    public static function lockup(string $kind): string
    {
        return match ($kind) {
            self::Characters, self::People => 'person',
            self::Episodes => 'episode',
            self::Songs => 'music',
            self::Studios => 'studio',
            default => 'small',
        };
    }
}
