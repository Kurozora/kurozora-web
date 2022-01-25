<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static People()
 * @method static static Characters()
 * @method static static Shows()
 * @method static static Genres()
 * @method static static Themes()
 * @method static static MostPopularShows()
 * @method static static UpcomingShows()
 */
final class ExploreCategoryTypes extends Enum
{
    const People = 'people';
    const Characters = 'characters';
    const Shows = 'shows';
    const Genres = 'genres';
    const Themes = 'themes';
    const MostPopularShows = 'most-popular-shows';
    const UpcomingShows = 'upcoming-shows';
}
