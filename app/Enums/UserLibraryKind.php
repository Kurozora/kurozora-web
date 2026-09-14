<?php declare(strict_types=1);

namespace App\Enums;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
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

    /**
     * The trackable type each sync stream carries.
     *
     * @return array
     */
    public static function syncStreams(): array
    {
        return [
            'shows' => Anime::class,
            'literatures' => Manga::class,
            'games' => Game::class,
        ];
    }

    /**
     * The kind value the given trackable type belongs to.
     *
     * @param string $morphClass
     *
     * @return int
     */
    public static function fromMorphClass(string $morphClass): int
    {
        return match ($morphClass) {
            Manga::class => self::Manga,
            Game::class => self::Game,
            default => self::Anime,
        };
    }
}
