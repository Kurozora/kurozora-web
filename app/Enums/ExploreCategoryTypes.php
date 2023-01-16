<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static MostPopularShows()
 * @method static static UpcomingShows()
 * @method static static AnimeContinuing()
 * @method static static AnimeSeason()
 * @method static static NewShows()
 * @method static static RecentlyUpdateShows()
 * @method static static Shows()
 * @method static static Characters()
 * @method static static People()
 * @method static static Genres()
 * @method static static Themes()
 * @method static static Songs()
 */
final class ExploreCategoryTypes extends Enum
{
    const MostPopularShows = 'most-popular-shows';
    const AnimeContinuing = 'anime-continuing';
    const AnimeSeason = 'anime-season';
    const UpcomingShows = 'upcoming-shows';
    const NewShows = 'new-shows';
    const RecentlyUpdateShows = 'recently-update-shows';
    const Shows = 'shows';
    const Characters = 'characters';
    const People = 'people';
    const Genres = 'genres';
    const Themes = 'themes';
    const Songs = 'songs';
}
