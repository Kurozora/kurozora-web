<?php declare(strict_types=1);

namespace App\Enums;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use BenSampo\Enum\Enum;

/**
 * @method static FavoriteKind Anime()
 * @method static FavoriteKind Manga()
 * @method static FavoriteKind Game()
 * @method static FavoriteKind Character()
 * @method static FavoriteKind Person()
 * @method static FavoriteKind Studio()
 * @method static FavoriteKind Song()
 */
final class FavoriteKind extends Enum
{
    const int Anime = 0;
    const int Manga = 1;
    const int Game = 2;
    const int Character = 3;
    const int Person = 4;
    const int Studio = 5;
    const int Song = 6;

    /**
     * Returns the kind of the given morph class.
     *
     * @param string $morphClass
     *
     * @return static|null
     */
    public static function fromMorphClass(string $morphClass): ?static
    {
        return match ($morphClass) {
            Anime::class => static::Anime(),
            Manga::class => static::Manga(),
            Game::class => static::Game(),
            Character::class => static::Character(),
            Person::class => static::Person(),
            Studio::class => static::Studio(),
            Song::class => static::Song(),
            default => null,
        };
    }

    /**
     * Returns whether the kind corresponds to a library-trackable entity.
     *
     * @return bool
     */
    public function isLibraryTrackable(): bool
    {
        return in_array($this->value, [self::Anime, self::Manga, self::Game], true);
    }
}
