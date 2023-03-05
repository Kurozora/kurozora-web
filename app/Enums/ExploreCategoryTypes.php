<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ExploreCategoryTypes Shows()
 * @method static ExploreCategoryTypes MostPopularShows()
 * @method static ExploreCategoryTypes ContinuingShows()
 * @method static ExploreCategoryTypes ShowsSeason()
 * @method static ExploreCategoryTypes UpcomingShows()
 * @method static ExploreCategoryTypes NewShows()
 * @method static ExploreCategoryTypes RecentlyUpdateShows()
 * @method static ExploreCategoryTypes RecentlyFinishedShows()
 * @method static ExploreCategoryTypes Literatures()
 * @method static ExploreCategoryTypes MostPopularLiteratures()
 * @method static ExploreCategoryTypes ContinuingLiteratures()
 * @method static ExploreCategoryTypes LiteraturesSeason()
 * @method static ExploreCategoryTypes UpcomingLiteratures()
 * @method static ExploreCategoryTypes NewLiteratures()
 * @method static ExploreCategoryTypes RecentlyUpdateLiteratures()
 * @method static ExploreCategoryTypes RecentlyFinishedLiteratures()
 * @method static ExploreCategoryTypes Games()
 * @method static ExploreCategoryTypes MostPopularGames()
 * @method static ExploreCategoryTypes GamesSeason()
 * @method static ExploreCategoryTypes UpcomingGames()
 * @method static ExploreCategoryTypes NewGames()
 * @method static ExploreCategoryTypes RecentlyUpdateGames()
 * @method static ExploreCategoryTypes Characters()
 * @method static ExploreCategoryTypes People()
 * @method static ExploreCategoryTypes Genres()
 * @method static ExploreCategoryTypes Themes()
 * @method static ExploreCategoryTypes Songs()
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
    const Games = 'games';
    const MostPopularGames = 'most-popular-games';
    const GamesSeason = 'games-season';
    const UpcomingGames = 'upcoming-games';
    const NewGames = 'new-games';
    const RecentlyUpdateGames = 'recently-update-games';
    const Characters = 'characters';
    const People = 'people';
    const Genres = 'genres';
    const Themes = 'themes';
    const Songs = 'songs';
}
