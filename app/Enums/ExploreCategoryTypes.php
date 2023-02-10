<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Shows()
 * @method static static MostPopularShows()
 * @method static static UpcomingShows()
 * @method static static AnimeContinuing()
 * @method static static AnimeSeason()
 * @method static static NewShows()
 * @method static static RecentlyUpdateShows()
 * @method static static RecentlyFinishedShows()
 * @method static static Manga()
 * @method static static MostPopularManga()
 * @method static static UpcomingManga()
 * @method static static MangaContinuing()
 * @method static static MangaSeason()
 * @method static static NewManga()
 * @method static static RecentlyUpdateManga()
 * @method static static RecentlyFinishedManga()
 * @method static static Characters()
 * @method static static People()
 * @method static static Genres()
 * @method static static Themes()
 * @method static static Songs()
 */
final class ExploreCategoryTypes extends Enum
{
    const Shows = 'shows';
    const MostPopularShows = 'most-popular-shows';
    const ContinuingShows = 'anime-continuing';
    const ShowsSeason = 'anime-season';
    const UpcomingShows = 'upcoming-shows';
    const NewShows = 'new-shows';
    const RecentlyUpdateShows = 'recently-update-shows';
    const RecentlyFinishedShows = 'recently-finished-shows';
    const Literatures = 'literatures';
    const MostPopularLiteratures = 'most-popular-literatures';
    const ContinuingLiteratures = 'literatures-continuing';
    const LiteraturesSeason = 'literatures-season';
    const UpcomingLiteratures = 'upcoming-literatures';
    const NewLiteratures = 'new-literatures';
    const RecentlyUpdateLiteratures = 'recently-update-literatures';
    const RecentlyFinishedLiteratures = 'recently-finished-literatures';
    const Characters = 'characters';
    const People = 'people';
    const Genres = 'genres';
    const Themes = 'themes';
    const Songs = 'songs';
}
