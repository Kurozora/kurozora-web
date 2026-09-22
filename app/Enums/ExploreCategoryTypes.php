<?php declare(strict_types=1);

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
 * @method static ExploreCategoryTypes UpNextEpisodes()
 * @method static ExploreCategoryTypes Episodes()
 * @method static ExploreCategoryTypes People()
 * @method static ExploreCategoryTypes Genres()
 * @method static ExploreCategoryTypes Themes()
 * @method static ExploreCategoryTypes Songs()
 * @method static ExploreCategoryTypes ReCAP()
 */
final class ExploreCategoryTypes extends Enum
{
    const string Shows = 'shows';
    const string MostPopularShows = 'most-popular-shows';
    const string ContinuingShows = 'anime-continuing';
    const string ShowsSeason = 'anime-season';
    const string UpcomingShows = 'upcoming-shows';
    const string NewShows = 'new-shows';
    const string RecentlyUpdateShows = 'recently-update-shows';
    const string RecentlyFinishedShows = 'recently-finished-shows';
    const string Literatures = 'literatures';
    const string MostPopularLiteratures = 'most-popular-literatures';
    const string ContinuingLiteratures = 'literatures-continuing';
    const string LiteraturesSeason = 'literatures-season';
    const string UpcomingLiteratures = 'upcoming-literatures';
    const string NewLiteratures = 'new-literatures';
    const string RecentlyUpdateLiteratures = 'recently-update-literatures';
    const string RecentlyFinishedLiteratures = 'recently-finished-literatures';
    const string Games = 'games';
    const string MostPopularGames = 'most-popular-games';
    const string GamesSeason = 'games-season';
    const string UpcomingGames = 'upcoming-games';
    const string NewGames = 'new-games';
    const string RecentlyUpdateGames = 'recently-update-games';
    const string Characters = 'characters';
    const string UpNextEpisodes = 'up-next-episodes';
    const string Episodes = 'episodes';
    const string People = 'people';
    const string Genres = 'genres';
    const string Themes = 'themes';
    const string Songs = 'songs';
    const string ReCAP = 'recap';

    /**
     * The library kind the given category type lists.
     *
     * @param string $type
     *
     * @return int|null
     */
    public static function libraryKind(string $type): ?int
    {
        return match ($type) {
            self::Shows, self::MostPopularShows, self::ContinuingShows, self::ShowsSeason, self::UpcomingShows, self::NewShows, self::RecentlyUpdateShows, self::RecentlyFinishedShows => UserLibraryKind::Anime,
            self::Literatures, self::MostPopularLiteratures, self::ContinuingLiteratures, self::LiteraturesSeason, self::UpcomingLiteratures, self::NewLiteratures, self::RecentlyUpdateLiteratures, self::RecentlyFinishedLiteratures => UserLibraryKind::Manga,
            self::Games, self::MostPopularGames, self::GamesSeason, self::UpcomingGames, self::NewGames, self::RecentlyUpdateGames => UserLibraryKind::Game,
            default => null,
        };
    }

    /**
     * The lockup the given category type renders.
     *
     * @param string      $type
     * @param string|null $size
     *
     * @return string
     */
    public static function lockup(string $type, ?string $size = null): string
    {
        return match ($type) {
            self::MostPopularShows => 'banner',
            self::UpcomingShows, self::UpcomingLiteratures, self::UpcomingGames => 'upcoming',
            self::Genres, self::Themes => 'medium',
            self::Characters, self::People => 'person',
            self::Songs => 'music',
            self::ReCAP => 'recap',
            self::UpNextEpisodes, self::Episodes => 'episode',
            self::Shows, self::Literatures, self::Games => $size === ExploreCategorySize::Video ? 'video' : 'small',
            default => 'small',
        };
    }
}
