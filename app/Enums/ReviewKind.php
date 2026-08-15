<?php declare(strict_types=1);

namespace App\Enums;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use BenSampo\Enum\Enum;

/**
 * @method static ReviewKind Anime()
 * @method static ReviewKind Manga()
 * @method static ReviewKind Game()
 * @method static ReviewKind Character()
 * @method static ReviewKind Person()
 * @method static ReviewKind Studio()
 * @method static ReviewKind Song()
 * @method static ReviewKind Episode()
 */
final class ReviewKind extends Enum
{
    const int Anime = 0;
    const int Manga = 1;
    const int Game = 2;
    const int Character = 3;
    const int Person = 4;
    const int Studio = 5;
    const int Song = 6;
    const int Episode = 7;

    /**
     * Returns the morph class of an enum value.
     *
     * @return string
     */
    public function getMorphClass(): string
    {
        return match ($this->value) {
            ReviewKind::Manga => Manga::class,
            ReviewKind::Game => Game::class,
            ReviewKind::Character => Character::class,
            ReviewKind::Person => Person::class,
            ReviewKind::Studio => Studio::class,
            ReviewKind::Song => Song::class,
            ReviewKind::Episode => Episode::class,
            default => Anime::class,
        };
    }
}
