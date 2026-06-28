<?php

namespace App\Services;

use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Enums\WeeklyDigestSection;
use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\MediaGenre;
use App\Models\MediaStaff;
use App\Models\Person;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLibrary;
use App\Models\UserWatchedEpisode;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class WeeklyDigestService
{
    /**
     * The library statuses counted as tracked.
     *
     * @var array
     */
    private const array TRACKED_STATUSES = [
        UserLibraryStatus::InProgress,
        UserLibraryStatus::Planning,
        UserLibraryStatus::Interested,
    ];

    /**
     * How many days ahead the "coming up" beat looks.
     *
     * @var int
     */
    private const int COMING_UP_DAYS = 7;

    /**
     * How many "coming up" entries to surface at most.
     *
     * @var int
     */
    private const int COMING_UP_LIMIT = 10;

    /**
     * Whether the build serves identity-only consumers.
     *
     * @var bool
     */
    private bool $identitiesOnly = false;

    /**
     * Builds a single named section of the weekly digest.
     *
     * @param User        $user
     * @param string      $type
     * @param Carbon|null $reference
     * @param bool        $identitiesOnly
     *
     * @return array
     */
    public function buildSection(User $user, string $type, ?Carbon $reference = null, bool $identitiesOnly = false): array
    {
        $this->identitiesOnly = $identitiesOnly;

        [$windowStart, $windowEnd] = $this->window($reference);

        return match ($type) {
            WeeklyDigestSection::Drops => $this->buildDrops($user, $windowStart, $windowEnd),
            WeeklyDigestSection::Recommendations => $this->buildRecommendations($user, $windowStart, $windowEnd),
            WeeklyDigestSection::Rescue => $this->buildRescue($user),
            WeeklyDigestSection::UpNext => $this->buildUpNext($user, $windowStart, $windowEnd),
            WeeklyDigestSection::Trending => ['trending' => $this->trendingEpisodes($user, $windowStart, $windowEnd)],
            WeeklyDigestSection::Birthdays => ['birthdays' => $this->birthdayPeople($this->allLibraryAnimeIDs($user))],
            WeeklyDigestSection::Momentum => $this->buildMomentum($user, $windowStart, $windowEnd),
            WeeklyDigestSection::Growth => $this->buildGrowth($windowStart, $windowEnd),
            default => [],
        };
    }

    /**
     * Builds every digest section at once.
     *
     * @param User        $user
     * @param Carbon|null $reference
     * @param bool        $identitiesOnly
     *
     * @return array
     */
    public function buildAll(User $user, ?Carbon $reference = null, bool $identitiesOnly = false): array
    {
        $digest = [];

        foreach (WeeklyDigestSection::getInstances() as $section) {
            $digest[$section->value] = $this->buildSection($user, $section->value, $reference, $identitiesOnly);
        }

        return $digest;
    }

    /**
     * Returns the start and end of the most recently completed week.
     *
     * @param Carbon|null $reference
     *
     * @return array
     */
    private function window(?Carbon $reference): array
    {
        $windowEnd = ($reference ?? Carbon::now())->copy()->startOfWeek(Carbon::MONDAY);

        return [$windowEnd->copy()->subWeek(), $windowEnd];
    }

    /**
     * Builds the hero anime and the week's episode and release drops.
     *
     * @param User   $user
     * @param Carbon $windowStart
     * @param Carbon $windowEnd
     *
     * @return array
     */
    private function buildDrops(User $user, Carbon $windowStart, Carbon $windowEnd): array
    {
        $trackedAnimeIDs = $this->trackedIDs($user, (new Anime)->getMorphClass());
        $trackedGameIDs = $this->trackedIDs($user, (new Game)->getMorphClass());

        $drops = $this->animeDrops($user, $trackedAnimeIDs, $windowStart, $windowEnd)
            ->merge($this->gameDrops($user, $trackedGameIDs, $windowStart, $windowEnd));

        $hero = $this->pickHero($drops);

        $airedEpisodes = $drops
            ->filter(fn (array $drop) => $drop['kind']->is(UserLibraryKind::Anime()))
            ->reject(fn (array $drop) => $hero !== null && $drop['model']->is($hero['model']))
            ->flatMap(fn (array $drop) => $drop['episodes']);

        return [
            'hero' => $hero,
            'heroCaption' => $hero !== null ? $this->heroCaption($hero) : null,
            'newEpisodes' => $airedEpisodes
                ->reject(fn (Episode $episode) => $episode->is_finale)
                ->sortByDesc(fn (Episode $episode) => $episode->started_at)
                ->values(),
            'finales' => $airedEpisodes
                ->filter(fn (Episode $episode) => $episode->is_finale)
                ->sortByDesc(fn (Episode $episode) => $episode->started_at)
                ->values(),
            'newReleases' => $drops
                ->filter(fn (array $drop) => $drop['kind']->is(UserLibraryKind::Game()))
                ->reject(fn (array $drop) => $hero !== null && $drop['model']->is($hero['model']))
                ->map(fn (array $drop) => $drop['model'])
                ->values(),
        ];
    }

    /**
     * Builds the because-you-watched and drop-in recommendations.
     *
     * @param User   $user
     * @param Carbon $windowStart
     * @param Carbon $windowEnd
     *
     * @return array
     */
    private function buildRecommendations(User $user, Carbon $windowStart, Carbon $windowEnd): array
    {
        $libraryAnimeIDs = $this->allLibraryAnimeIDs($user);

        return [
            'becauseYouWatched' => $this->becauseYouWatched($user, $libraryAnimeIDs, $windowStart, $windowEnd),
            'dropIn' => $this->dropInRecommendation($user, $libraryAnimeIDs),
        ];
    }

    /**
     * Builds the anime to pick back up and the plan-to-watch backlog.
     *
     * @param User $user
     *
     * @return array
     */
    private function buildRescue(User $user): array
    {
        return [
            'onHold' => $this->staleByStatus($user, UserLibraryStatus::OnHold),
            'planning' => $this->staleByStatus($user, UserLibraryStatus::Planning),
        ];
    }

    /**
     * Builds the anime premiering and games releasing over the coming days.
     *
     * @param User   $user
     * @param Carbon $windowStart
     * @param Carbon $windowEnd
     *
     * @return array
     */
    private function buildUpNext(User $user, Carbon $windowStart, Carbon $windowEnd): array
    {
        $trackedAnimeIDs = $this->trackedIDs($user, (new Anime)->getMorphClass());
        $trackedGameIDs = $this->trackedIDs($user, (new Game)->getMorphClass());
        $comingUp = $this->comingUp($user, $trackedAnimeIDs, $trackedGameIDs);

        return [
            'premiering' => $comingUp->filter(fn (array $entry) => $entry['kind']->is(UserLibraryKind::Anime()))->map(fn (array $entry) => $entry['model'])->values(),
            'releasing' => $comingUp->filter(fn (array $entry) => $entry['kind']->is(UserLibraryKind::Game()))->map(fn (array $entry) => $entry['model'])->values(),
        ];
    }

    /**
     * Builds the user's momentum stats.
     *
     * @param User   $user
     * @param Carbon $windowStart
     * @param Carbon $windowEnd
     *
     * @return array
     * @throws Exception
     */
    private function buildMomentum(User $user, Carbon $windowStart, Carbon $windowEnd): array
    {
        $momentum = $this->momentum($user, $windowStart, $windowEnd);

        return [
            'momentum' => $momentum,
            'hasMomentum' => $momentum['episodesWatched'] > 0 || $momentum['finishedCount'] > 0,
            'watchedTime' => $momentum['secondsWatched'] > 0
                ? CarbonInterval::seconds($momentum['secondsWatched'])->cascade()->forHumans(['short' => true, 'parts' => 2])
                : null,
            'milestone' => $momentum['lifetimeEpisodes'] > 0
                ? __('Only :x episodes to :y watched episodes!', ['x' => number_format($momentum['episodesToMilestone']), 'y' => number_format($momentum['nextMilestone'])])
                : null,
            'streak' => $momentum['weekStreak'] >= 2
                ? __('On a :x-week watch streak!', ['x' => $momentum['weekStreak']])
                : null,
        ];
    }

    /**
     * Builds the catalog growth summary for the week.
     *
     * @param Carbon $windowStart
     * @param Carbon $windowEnd
     *
     * @return array
     */
    private function buildGrowth(Carbon $windowStart, Carbon $windowEnd): array
    {
        $growth = $this->databaseGrowth($windowStart, $windowEnd);

        return [
            'animeCount' => $growth['anime'],
            'characterCount' => $growth['characters'],
            'peopleCount' => $growth['people'],
            'hasGrowth' => $growth['anime'] > 0 || $growth['characters'] > 0 || $growth['people'] > 0,
            'label' => __('This week on :app: :anime anime, :characters characters and :people people added.', [
                'app' => config('app.name'),
                'anime' => number_format($growth['anime']),
                'characters' => number_format($growth['characters']),
                'people' => number_format($growth['people']),
            ]),
        ];
    }

    /**
     * Returns the hero's caption.
     *
     * @param array $hero
     *
     * @return string
     */
    private function heroCaption(array $hero): string
    {
        if ($hero['episodes']->count() === 1) {
            return __('Episode :number is out', ['number' => $hero['episodes']->first()->number]);
        }

        return __(':count new episodes', ['count' => $hero['episodes']->count()]);
    }

    /**
     * Returns the ids of every anime in the user's library, regardless of status.
     *
     * @param User $user
     *
     * @return Collection
     */
    private function allLibraryAnimeIDs(User $user): Collection
    {
        return UserLibrary::where('user_id', '=', $user->id)
            ->where('trackable_type', '=', (new Anime)->getMorphClass())
            ->pluck('trackable_id');
    }

    /**
     * Returns the people relevant to the user's library with a birthday over the coming days.
     *
     * @param Collection $libraryAnimeIDs
     *
     * @return Collection
     * @throws ConnectionException
     */
    private function birthdayPeople(Collection $libraryAnimeIDs): Collection
    {
        if ($libraryAnimeIDs->isEmpty()) {
            return collect();
        }

        $now = Carbon::now();
        $days = collect(range(0, self::COMING_UP_DAYS - 1))
            ->map(fn (int $offset) => $now->copy()->addDays($offset));

        // Match the birthday in the database against the library-connected people via a join,
        // so each query returns only the handful of upcoming birthdays instead of every
        // connected person id.
        $birthdayFilter = function ($query) use ($days) {
            $query->whereNotNull(Person::TABLE_NAME . '.birthdate')
                ->where(function ($dayGroup) use ($days) {
                    foreach ($days as $day) {
                        $dayGroup->orWhere(fn ($dayQuery) => $dayQuery
                            ->whereMonth(Person::TABLE_NAME . '.birthdate', $day->month)
                            ->whereDay(Person::TABLE_NAME . '.birthdate', $day->day));
                    }
                });
        };

        $staffIDs = Person::query()
            ->join(MediaStaff::TABLE_NAME, MediaStaff::TABLE_NAME . '.person_id', '=', Person::TABLE_NAME . '.id')
            ->where(MediaStaff::TABLE_NAME . '.model_type', '=', (new Anime)->getMorphClass())
            ->whereIn(MediaStaff::TABLE_NAME . '.model_id', $libraryAnimeIDs)
            ->whereNull(MediaStaff::TABLE_NAME . '.deleted_at')
            ->where($birthdayFilter)
            ->distinct()
            ->limit(self::COMING_UP_LIMIT)
            ->pluck(Person::TABLE_NAME . '.id');

        $castIDs = Person::query()
            ->join(AnimeCast::TABLE_NAME, AnimeCast::TABLE_NAME . '.person_id', '=', Person::TABLE_NAME . '.id')
            ->whereIn(AnimeCast::TABLE_NAME . '.anime_id', $libraryAnimeIDs)
            ->whereNull(AnimeCast::TABLE_NAME . '.deleted_at')
            ->where($birthdayFilter)
            ->distinct()
            ->limit(self::COMING_UP_LIMIT)
            ->pluck(Person::TABLE_NAME . '.id');

        $personIDs = $staffIDs->merge($castIDs)->unique()->take(self::COMING_UP_LIMIT);

        if ($personIDs->isEmpty()) {
            return collect();
        }

        return Person::whereIn('id', $personIDs)
            ->when(!$this->identitiesOnly, fn ($query) => $query->with('media'))
            ->get();
    }

    /**
     * Returns a highly-ranked, finished anime in the user's favourite genre that they haven't started.
     *
     * @param User       $user
     * @param Collection $libraryAnimeIDs
     *
     * @return Anime|null
     */
    private function dropInRecommendation(User $user, Collection $libraryAnimeIDs): ?Anime
    {
        if ($libraryAnimeIDs->isEmpty()) {
            return null;
        }

        $topGenreID = MediaGenre::where('model_type', '=', (new Anime)->getMorphClass())
            ->whereIn('model_id', $libraryAnimeIDs)
            ->selectRaw('genre_id, COUNT(*) as genre_count')
            ->groupBy('genre_id')
            ->orderByDesc('genre_count')
            ->limit(1)
            ->value('genre_id');

        if ($topGenreID === null) {
            return null;
        }

        return Anime::whereHas('genres', fn ($query) => $query->whereKey($topGenreID))
            ->whereHas('status', fn ($query) => $query->where('name', '=', 'Finished Airing'))
            ->whereNotIn('id', $libraryAnimeIDs)
            ->with($this->lockupRelations($user))
            ->orderByDesc('rank_total')
            ->first();
    }

    /**
     * Returns the number of consecutive recent weeks the user has logged an episode.
     *
     * @param User $user
     *
     * @return int
     */
    private function watchStreak(User $user): int
    {
        $weekStarts = UserWatchedEpisode::completed()
            ->where('user_id', '=', $user->id)
            ->selectRaw('DATE(DATE_SUB(completed_at, INTERVAL WEEKDAY(completed_at) DAY)) as week_start')
            ->distinct()
            ->orderByDesc('week_start')
            ->pluck('week_start');

        if ($weekStarts->isEmpty()) {
            return 0;
        }

        $currentWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $cursor = Carbon::parse($weekStarts->first())->startOfDay();

        // The streak only counts if the most recent logged week is this week or last week.
        if ($cursor->lessThan($currentWeek->copy()->subWeek())) {
            return 0;
        }

        $streak = 0;

        foreach ($weekStarts as $weekStart) {
            $weekStart = Carbon::parse($weekStart)->startOfDay();

            if ($weekStart->equalTo($cursor)) {
                $streak++;
                $cursor = $cursor->copy()->subWeek();
            } else if ($weekStart->lessThan($cursor)) {
                break;
            }
        }

        return $streak;
    }

    /**
     * Returns the anime related to the show the user watched most this week.
     *
     * @param User       $user
     * @param Collection $libraryAnimeIDs
     * @param Carbon     $windowStart
     * @param Carbon     $windowEnd
     *
     * @return array
     */
    private function becauseYouWatched(User $user, Collection $libraryAnimeIDs, Carbon $windowStart, Carbon $windowEnd): array
    {
        $topAnimeID = UserWatchedEpisode::where(UserWatchedEpisode::TABLE_NAME . '.user_id', '=', $user->id)
            ->whereBetween(UserWatchedEpisode::TABLE_NAME . '.completed_at', [$windowStart, $windowEnd])
            ->join(Episode::TABLE_NAME, Episode::TABLE_NAME . '.id', '=', UserWatchedEpisode::TABLE_NAME . '.episode_id')
            ->join(Season::TABLE_NAME, Season::TABLE_NAME . '.id', '=', Episode::TABLE_NAME . '.season_id')
            ->groupBy(Season::TABLE_NAME . '.anime_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->value(Season::TABLE_NAME . '.anime_id');

        if ($topAnimeID === null) {
            return ['anime' => null, 'relations' => collect()];
        }

        $anime = Anime::with('translation')->find($topAnimeID);

        if ($anime === null) {
            return ['anime' => null, 'relations' => collect()];
        }

        $relations = $anime->animeRelations()
            ->with([
                'related' => fn ($query) => $query->with($this->lockupRelations($user)),
                'relation',
            ])
            ->get()
            ->filter(fn ($relation) => $relation->related instanceof Anime && !$libraryAnimeIDs->contains($relation->related->getKey()))
            ->take(self::COMING_UP_LIMIT)
            ->values();

        return ['anime' => $anime, 'relations' => $relations];
    }

    /**
     * Returns the most-watched episodes across the platform this week.
     *
     * @param User   $user
     * @param Carbon $windowStart
     * @param Carbon $windowEnd
     *
     * @return Collection
     */
    private function trendingEpisodes(User $user, Carbon $windowStart, Carbon $windowEnd): Collection
    {
        // Global and over a completed week, so cache the (expensive) aggregate. Only
        // episodes that aired recently, so evergreen first-episodes don't dominate.
        $episodeIDs = Cache::remember('digest:trending:' . $windowStart->format('Y-m-d'), now()->addDay(), function () use ($windowStart, $windowEnd) {
            return UserWatchedEpisode::whereBetween(UserWatchedEpisode::TABLE_NAME . '.completed_at', [$windowStart, $windowEnd])
                ->join(Episode::TABLE_NAME, Episode::TABLE_NAME . '.id', '=', UserWatchedEpisode::TABLE_NAME . '.episode_id')
                ->where(Episode::TABLE_NAME . '.started_at', '>=', now()->subDays(30))
                ->selectRaw(UserWatchedEpisode::TABLE_NAME . '.episode_id as episode_id, COUNT(*) as watch_count')
                ->groupBy(UserWatchedEpisode::TABLE_NAME . '.episode_id')
                ->orderByDesc('watch_count')
                ->limit(3)
                ->pluck('episode_id')
                ->all();
        });

        if (empty($episodeIDs)) {
            return collect();
        }

        return Episode::whereIn('id', $episodeIDs)
            ->with($this->episodeRelations($user))
            ->withExists(['userWatchedEpisodes as isWatched' => fn ($query) => $query->where('user_id', '=', $user->id)->completed()])
            ->get();
    }

    /**
     * Returns the tracked anime in the given status that the user hasn't touched in months.
     *
     * @param User $user
     * @param int  $status
     *
     * @return Collection
     */
    private function staleByStatus(User $user, int $status): Collection
    {
        $animeIDs = UserLibrary::where('user_id', '=', $user->id)
            ->where('trackable_type', '=', (new Anime)->getMorphClass())
            ->where('is_hidden', '=', false)
            ->where('status', '=', $status)
            ->where('updated_at', '<', Carbon::now()->subMonths(3))
            ->orderBy('updated_at')
            ->limit(self::COMING_UP_LIMIT)
            ->pluck('trackable_id');

        if ($animeIDs->isEmpty()) {
            return collect();
        }

        return Anime::whereIn('id', $animeIDs)
            ->with($this->lockupRelations($user))
            ->get();
    }

    /**
     * Returns the catalog entries added over the window.
     *
     * @param Carbon $windowStart
     * @param Carbon $windowEnd
     *
     * @return array
     */
    private function databaseGrowth(Carbon $windowStart, Carbon $windowEnd): array
    {
        return Cache::remember('digest:growth:' . $windowStart->format('Y-m-d'), now()->addDay(), fn () => [
            'anime' => Anime::whereBetween('created_at', [$windowStart, $windowEnd])->count(),
            'characters' => Character::whereBetween('created_at', [$windowStart, $windowEnd])->count(),
            'people' => Person::whereBetween('created_at', [$windowStart, $windowEnd])->count(),
        ]);
    }

    /**
     * Returns the ids of the titles of the given morph type the user actively tracks.
     *
     * @param User   $user
     * @param string $morphType
     *
     * @return Collection
     */
    private function trackedIDs(User $user, string $morphType): Collection
    {
        return UserLibrary::where('user_id', '=', $user->id)
            ->where('trackable_type', '=', $morphType)
            ->where('is_hidden', '=', false)
            ->whereIn('status', self::TRACKED_STATUSES)
            ->pluck('trackable_id');
    }

    /**
     * Returns the relations a media lockup needs to render.
     *
     * @param User $user
     *
     * @return array
     */
    private function lockupRelations(User $user): array
    {
        if ($this->identitiesOnly) {
            return [
                'library' => fn ($libraryQuery) => $libraryQuery->where('user_id', '=', $user->id),
            ];
        }

        return [
            'genres',
            'mediaStat',
            'media',
            'translation',
            'tvRating',
            'themes',
            'library' => fn ($libraryQuery) => $libraryQuery->where('user_id', '=', $user->id),
        ];
    }

    /**
     * Returns the relations an episode needs to render.
     *
     * @param User $user
     *
     * @return array
     */
    private function episodeRelations(User $user): array
    {
        if ($this->identitiesOnly) {
            return [
                'anime' => fn ($query) => $query->with($this->lockupRelations($user)),
            ];
        }

        return [
            'media',
            'translation',
            'season',
            'anime' => fn ($query) => $query->with($this->lockupRelations($user)),
        ];
    }

    /**
     * Returns the tracked anime whose episodes aired during the window.
     *
     * @param User       $user
     * @param Collection $animeIDs
     * @param Carbon     $windowStart
     * @param Carbon     $windowEnd
     *
     * @return Collection
     */
    private function animeDrops(User $user, Collection $animeIDs, Carbon $windowStart, Carbon $windowEnd): Collection
    {
        if ($animeIDs->isEmpty()) {
            return collect();
        }

        $episodes = Episode::whereBetween('started_at', [$windowStart, $windowEnd])
            ->whereHas('season', fn (Builder $query) => $query->whereIn('anime_id', $animeIDs))
            ->with($this->episodeRelations($user))
            ->withExists(['userWatchedEpisodes as isWatched' => fn ($query) => $query->where('user_id', '=', $user->id)->completed()])
            ->orderBy('started_at')
            ->get()
            ->filter(fn (Episode $episode) => $episode->anime !== null);

        $watchedCounts = $this->watchedCountsByAnime($user, $episodes->pluck('anime.id')->unique());

        return $episodes
            ->groupBy(fn (Episode $episode) => $episode->anime->getKey())
            ->map(function (Collection $group) use ($watchedCounts) {
                $anime = $group->first()->anime;

                return [
                    'kind' => UserLibraryKind::Anime(),
                    'model' => $anime,
                    'episodes' => $group->values(),
                    'watchedCount' => (int) ($watchedCounts[$anime->getKey()] ?? 0),
                    'totalCount' => (int) $anime->episode_count,
                ];
            })
            ->values();
    }

    /**
     * Returns the tracked games released during the window.
     *
     * @param User       $user
     * @param Collection $gameIDs
     * @param Carbon     $windowStart
     * @param Carbon     $windowEnd
     *
     * @return Collection
     */
    private function gameDrops(User $user, Collection $gameIDs, Carbon $windowStart, Carbon $windowEnd): Collection
    {
        if ($gameIDs->isEmpty()) {
            return collect();
        }

        return Game::whereBetween('published_at', [$windowStart, $windowEnd])
            ->whereIn('id', $gameIDs)
            ->with($this->lockupRelations($user))
            ->orderBy('published_at')
            ->get()
            ->map(fn (Game $game) => [
                'kind' => UserLibraryKind::Game(),
                'model' => $game,
                'episodes' => collect(),
                'watchedCount' => 0,
                'totalCount' => 0,
            ])
            ->values();
    }

    /**
     * Returns the user's watched episode counts for the given anime.
     *
     * @param User       $user
     * @param Collection $animeIDs
     *
     * @return Collection
     */
    private function watchedCountsByAnime(User $user, Collection $animeIDs): Collection
    {
        if ($animeIDs->isEmpty()) {
            return collect();
        }

        return UserWatchedEpisode::completed()
            ->where(UserWatchedEpisode::TABLE_NAME . '.user_id', '=', $user->id)
            ->join(Episode::TABLE_NAME, Episode::TABLE_NAME . '.id', '=', UserWatchedEpisode::TABLE_NAME . '.episode_id')
            ->join(Season::TABLE_NAME, Season::TABLE_NAME . '.id', '=', Episode::TABLE_NAME . '.season_id')
            ->whereIn(Season::TABLE_NAME . '.anime_id', $animeIDs)
            ->groupBy(Season::TABLE_NAME . '.anime_id')
            ->selectRaw(Season::TABLE_NAME . '.anime_id as anime_id, count(*) as watched_count')
            ->pluck('watched_count', 'anime_id');
    }

    /**
     * Returns the standout drop of the week.
     *
     * @param Collection $drops
     *
     * @return array|null
     */
    private function pickHero(Collection $drops): ?array
    {
        $animeDrops = $drops->filter(fn (array $drop) => $drop['kind']->is(UserLibraryKind::Anime()));

        if ($animeDrops->isEmpty()) {
            return null;
        }

        return $animeDrops->sortByDesc(function (array $drop) {
            $library = $drop['model']->library->first();
            $isInProgress = $library !== null && (int) $library->status === UserLibraryStatus::InProgress;

            return [$isInProgress ? 1 : 0, (int) $drop['model']->rank_total];
        })->first();
    }

    /**
     * Returns the user's consumption stats for the window.
     *
     * @param User   $user
     * @param Carbon $windowStart
     * @param Carbon $windowEnd
     *
     * @return array
     */
    private function momentum(User $user, Carbon $windowStart, Carbon $windowEnd): array
    {
        $episodesWatched = UserWatchedEpisode::where('user_id', '=', $user->id)
            ->whereBetween('completed_at', [$windowStart, $windowEnd])
            ->count();

        $secondsWatched = (int) UserWatchedEpisode::where(UserWatchedEpisode::TABLE_NAME . '.user_id', '=', $user->id)
            ->whereBetween(UserWatchedEpisode::TABLE_NAME . '.completed_at', [$windowStart, $windowEnd])
            ->join(Episode::TABLE_NAME, Episode::TABLE_NAME . '.id', '=', UserWatchedEpisode::TABLE_NAME . '.episode_id')
            ->sum(Episode::TABLE_NAME . '.duration');

        $finishedCount = UserLibrary::where('user_id', '=', $user->id)
            ->whereBetween('ended_at', [$windowStart, $windowEnd])
            ->count();

        $lifetimeEpisodes = UserWatchedEpisode::completed()->where('user_id', '=', $user->id)->count();
        $nextMilestone = (intdiv($lifetimeEpisodes, 100) + 1) * 100;

        return [
            'episodesWatched' => $episodesWatched,
            'secondsWatched' => $secondsWatched,
            'finishedCount' => $finishedCount,
            'lifetimeEpisodes' => $lifetimeEpisodes,
            'nextMilestone' => $nextMilestone,
            'episodesToMilestone' => $nextMilestone - $lifetimeEpisodes,
            'weekStreak' => $this->watchStreak($user),
        ];
    }

    /**
     * Returns the tracked titles releasing over the coming days, soonest first.
     *
     * @param User       $user
     * @param Collection $animeIDs
     * @param Collection $gameIDs
     *
     * @return Collection
     */
    private function comingUp(User $user, Collection $animeIDs, Collection $gameIDs): Collection
    {
        $now = Carbon::now();
        $until = $now->copy()->addDays(self::COMING_UP_DAYS);

        $premieringAnime = $animeIDs->isEmpty()
            ? collect()
            : Anime::whereBetween('started_at', [$now, $until])
                ->whereIn('id', $animeIDs)
                ->with($this->lockupRelations($user))
                ->orderBy('started_at')
                ->get()
                ->map(fn (Anime $anime) => [
                    'kind' => UserLibraryKind::Anime(),
                    'model' => $anime,
                    'releaseAt' => $anime->started_at,
                ]);

        $releasingGames = $gameIDs->isEmpty()
            ? collect()
            : Game::whereBetween('published_at', [$now, $until])
                ->whereIn('id', $gameIDs)
                ->with($this->lockupRelations($user))
                ->orderBy('published_at')
                ->get()
                ->map(fn (Game $game) => [
                    'kind' => UserLibraryKind::Game(),
                    'model' => $game,
                    'releaseAt' => $game->published_at,
                ]);

        return $premieringAnime->merge($releasingGames)
            ->sortBy('releaseAt')
            ->take(self::COMING_UP_LIMIT)
            ->values();
    }
}
