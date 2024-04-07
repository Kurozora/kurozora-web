<?php declare(strict_types=1);

namespace App\Enums;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use BenSampo\Enum\Enum;

/**
 * @method static MediaType Episodes()
 * @method static MediaType Games()
 * @method static MediaType Literatures()
 * @method static MediaType Shows()
 */
final class MediaType extends Enum
{
    const string Episodes = 'episodes';
    const string Games = 'games';
    const string Literatures = 'literatures';
    const string Shows = 'shows';

    /**
     * Get the model value of a media type.
     *
     * @return String
     */
    function toModel(): String
    {
        return match($this->value) {
            self::Episodes => Episode::class,
            self::Games => Game::class,
            self::Literatures => Manga::class,
            self::Shows => Anime::class,
        };
    }
}
