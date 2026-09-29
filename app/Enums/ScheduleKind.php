<?php declare(strict_types=1);

namespace App\Enums;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use BenSampo\Enum\Enum;

/**
 * @method static ScheduleKind Anime()
 * @method static ScheduleKind Manga()
 * @method static ScheduleKind Game()
 */
final class ScheduleKind extends Enum
{
    const int Anime = 0;
    const int Manga = 1;
    const int Game = 2;

    /**
     * The model class scheduled under the given query type.
     *
     * @param string $type
     *
     * @return string
     */
    public static function modelClass(string $type): string
    {
        return match ($type) {
            strtolower(self::Game()->key) => Game::class,
            strtolower(self::Manga()->key) => Manga::class,
            default => Anime::class,
        };
    }

    /**
     * The query type of the given model class.
     *
     * @param string $modelClass
     *
     * @return string
     */
    public static function typeFor(string $modelClass): string
    {
        return strtolower(match ($modelClass) {
            Game::class => self::Game()->key,
            Manga::class => self::Manga()->key,
            default => self::Anime()->key,
        });
    }
}
