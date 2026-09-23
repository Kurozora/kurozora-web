<?php

namespace App\Console\Commands\Generators;

use App\Enums\RecapStatType;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Manga;
use App\Models\MediaGenre;
use App\Models\MediaRating;
use App\Models\MediaStaff;
use App\Models\MediaStudio;
use App\Models\MediaTheme;
use App\Models\Person;
use App\Models\Provider;
use App\Models\Recap;
use App\Models\RecapItem;
use App\Models\RecapStat;
use App\Models\Season;
use App\Models\StaffRole;
use App\Models\Studio;
use App\Models\Theme;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserFavorite;
use App\Models\UserLibrary;
use App\Models\UserWatchedEpisode;
use App\Scopes\MorphTvRatingScope;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Telescope\Telescope;
use Pulse;
use Throwable;

class GenerateRecaps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:recaps
                            {userID=all : The user for whom the recap is generated. ID|all}
                            {year? : The year of the recap. Empty for current year}
                            {month? : The month of the recap. Empty all months}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Kurozora Recaps for the specified users';

    /**
     * The number of users processed per chunk.
     *
     * @var int
     */
    const int CHUNK_SIZE = 100;

    /**
     * The number of ranked items kept per recap.
     *
     * @var int
     */
    const int ITEM_LIMIT = 15;

    /**
     * The number of ranked characters, voices and creators kept per recap.
     *
     * @var int
     */
    const int CREDIT_ITEM_LIMIT = 10;

    /**
     * The trackable types ranked by the recap.
     *
     * @var array
     */
    const array TRACKABLE_TYPES = [Anime::class, Manga::class, Game::class];

    /**
     * The first moment of the recap period.
     *
     * @var string
     */
    protected string $startedAt;

    /**
     * The last moment of the recap period.
     *
     * @var string
     */
    protected string $endedAt;

    /**
     * The year of the recap.
     *
     * @var int
     */
    protected int $year;

    /**
     * The month of the recap.
     *
     * @var int
     */
    protected int $recapMonth;

    /**
     * The ID of the Japanese language.
     *
     * @var int|null
     */
    protected ?int $japaneseLanguageID;

    /**
     * The ID of the supporting character cast role.
     *
     * @var int|null
     */
    protected ?int $supportingCastRoleID;

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Throwable
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();
        DB::connection()->disableQueryLog();

        $user = $this->argument('userID');
        $month = $this->argument('month');

        $this->year = (int) ($this->argument('year') ?? now()->year);
        $this->recapMonth = (int) ($month ?? 0);
        $this->startedAt = Carbon::createFromDate($this->year, (int) ($month ?? 1), 1)
            ->startOfDay()
            ->toDateTimeString();
        $this->endedAt = Carbon::createFromDate($this->year, (int) ($month ?: 12), 1)
            ->endOfMonth()
            ->endOfDay()
            ->toDateTimeString();
        $this->japaneseLanguageID = Language::where('code', '=', 'ja')->value('id');
        $this->supportingCastRoleID = CastRole::where('name', '=', 'Supporting Character')->value('id');

        $processedCount = 0;

        User::withoutGlobalScopes()
            ->select(['id'])
            ->when($user != 'all', function (Builder $query) use ($user) {
                $query->where('id', '=', $user);
            }, function (Builder $query) {
                $this->whereActive($query);
            })
            ->chunkById(self::CHUNK_SIZE, function ($users) use (&$processedCount) {
                $this->generateChunk($users->pluck('id')->all());

                $processedCount += $users->count();
                $this->line('Generated recaps for ' . $processedCount . ' users (' . round(memory_get_usage(true) / 1048576) . ' MB)');

                gc_collect_cycles();
            });

        if ($user == 'all') {
            $this->deleteInactiveRecaps();
        }

        $this->calculateTopPercentiles();

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Limits the query to the users with activity in the recap period.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    protected function whereActive(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereHas('library', function (Builder $query) {
                $this->whereLibraryInPeriod($query, UserLibrary::TABLE_NAME);
            })
                ->orWhereHas('userWatchedEpisodes', function (Builder $query) {
                    $query->whereBetween(UserWatchedEpisode::TABLE_NAME . '.completed_at', [$this->startedAt, $this->endedAt]);
                });
        });
    }

    /**
     * Limits the query to the library entries started within the recap period.
     *
     * @param mixed  $query
     * @param string $table
     *
     * @return mixed
     */
    protected function whereLibraryInPeriod(mixed $query, string $table): mixed
    {
        return $query->where(function ($query) use ($table) {
            $query->where([
                [$table . '.started_at', '>=', $this->startedAt],
                [$table . '.started_at', '<=', $this->endedAt],
            ])
                ->orWhere(function ($query) use ($table) {
                    $query->where([
                        [$table . '.started_at', '>=', $this->startedAt],
                        [$table . '.ended_at', '<=', $this->endedAt],
                    ]);
                });
        });
    }

    /**
     * Generates and saves the recaps of the given users.
     *
     * @param array $userIDs
     *
     * @return void
     * @throws Throwable
     */
    protected function generateChunk(array $userIDs): void
    {
        $library = $this->loadLibrary($userIDs);
        $titles = $this->loadTitles($library);
        $ratings = $this->loadRatings($userIDs);
        $watched = $this->loadWatchedEpisodes($userIDs);
        $credits = $this->loadCredits($titles, $watched);
        $activityCounts = $this->loadActivityCounts($userIDs);
        $recapRows = [];
        $recapItems = [];
        $statRows = [];

        foreach ($userIDs as $userID) {
            $userRecaps = $this->generateUserRecaps(
                $library[$userID] ?? [],
                $titles,
                $ratings[$userID] ?? [],
                $watched['anime'][$userID] ?? [],
                $credits
            );

            foreach ($userRecaps as $type => $recap) {
                $recapRows[] = [
                    'user_id' => $userID,
                    'year' => $this->year,
                    'month' => $this->recapMonth,
                    'type' => $type,
                    'total_series_count' => $recap['total_series_count'],
                    'total_parts_count' => $recap['total_parts_count'],
                    'total_parts_duration' => $recap['total_parts_duration'],
                ];
                $recapItems[$userID . ':' . $type] = $recap['items'];
            }

            array_push($statRows, ...$this->generateUserStats($userID, $watched, $activityCounts));
        }

        $this->saveChunk($userIDs, $recapRows, $recapItems, $statRows);
    }

    /**
     * The library entries of the given users started within the recap period.
     *
     * @param array $userIDs
     *
     * @return array
     */
    protected function loadLibrary(array $userIDs): array
    {
        $library = [];

        $this->whereLibraryInPeriod(UserLibrary::whereIn('user_id', $userIDs), UserLibrary::TABLE_NAME)
            ->toBase()
            ->get(['user_id', 'trackable_type', 'trackable_id', 'status'])
            ->each(function ($userLibrary) use (&$library) {
                $library[$userLibrary->user_id][] = [
                    'type' => $userLibrary->trackable_type,
                    'id' => (int) $userLibrary->trackable_id,
                    'status' => (int) $userLibrary->status,
                ];
            });

        return $library;
    }

    /**
     * The measures, genres and themes of the titles in the given library entries.
     *
     * @param array $library
     *
     * @return array
     */
    protected function loadTitles(array $library): array
    {
        $titleIDs = array_fill_keys(self::TRACKABLE_TYPES, []);

        foreach ($library as $userLibrary) {
            foreach ($userLibrary as $entry) {
                if (isset($titleIDs[$entry['type']])) {
                    $titleIDs[$entry['type']][$entry['id']] = true;
                }
            }
        }

        $titles = [
            'measures' => array_fill_keys(self::TRACKABLE_TYPES, []),
            'genres' => array_fill_keys(self::TRACKABLE_TYPES, []),
            'themes' => array_fill_keys(self::TRACKABLE_TYPES, []),
        ];

        foreach ($titleIDs as $type => $ids) {
            if (empty($ids)) {
                continue;
            }

            $ids = array_keys($ids);
            $columns = match ($type) {
                Manga::class => ['id', 'duration', 'chapter_count', 'page_count'],
                Game::class => ['id', 'duration'],
                default => ['id'],
            };

            foreach (array_chunk($ids, 1000) as $idsChunk) {
                $type::withoutGlobalScopes()
                    ->whereIn('id', $idsChunk)
                    ->toBase()
                    ->get($columns)
                    ->each(function ($title) use (&$titles, $type) {
                        $titles['measures'][$type][$title->id] = [
                            'duration' => (int) ($title->duration ?? 0),
                            'chapter_count' => (int) ($title->chapter_count ?? 0),
                            'page_count' => (int) ($title->page_count ?? 0),
                        ];
                    });

                MediaGenre::where('model_type', '=', $type)
                    ->whereIn('model_id', $idsChunk)
                    ->toBase()
                    ->get(['model_id', 'genre_id'])
                    ->each(function ($mediaGenre) use (&$titles, $type) {
                        $titles['genres'][$type][$mediaGenre->model_id][] = (int) $mediaGenre->genre_id;
                    });

                MediaTheme::where('model_type', '=', $type)
                    ->whereIn('model_id', $idsChunk)
                    ->toBase()
                    ->get(['model_id', 'theme_id'])
                    ->each(function ($mediaTheme) use (&$titles, $type) {
                        $titles['themes'][$type][$mediaTheme->model_id][] = (int) $mediaTheme->theme_id;
                    });
            }
        }

        return $titles;
    }

    /**
     * The ratings of the given users keyed by user and title.
     *
     * @param array $userIDs
     *
     * @return array
     */
    protected function loadRatings(array $userIDs): array
    {
        $ratings = [];

        MediaRating::whereIn('user_id', $userIDs)
            ->whereIn('model_type', self::TRACKABLE_TYPES)
            ->toBase()
            ->select(['user_id', 'model_type', 'model_id', 'rating'])
            ->selectRaw("(description IS NOT NULL AND description <> '') as has_description")
            ->get()
            ->each(function ($mediaRating) use (&$ratings) {
                $ratings[$mediaRating->user_id][$mediaRating->model_type . ':' . $mediaRating->model_id] = [
                    'type' => $mediaRating->model_type,
                    'rating' => (float) $mediaRating->rating,
                    'has_description' => (bool) $mediaRating->has_description,
                ];
            });

        return $ratings;
    }

    /**
     * The watched episode aggregates of the given users.
     *
     * @param array $userIDs
     *
     * @return array
     */
    protected function loadWatchedEpisodes(array $userIDs): array
    {
        $completedAt = UserWatchedEpisode::TABLE_NAME . '.completed_at';
        $watched = [
            'anime' => [],
            'days' => [],
            'weekdays' => [],
            'hours' => [],
            'providers' => [],
        ];

        $this->watchedEpisodesQuery($userIDs)
            ->groupBy([UserWatchedEpisode::TABLE_NAME . '.user_id', Season::TABLE_NAME . '.anime_id'])
            ->selectRaw('user_watched_episodes.user_id, seasons.anime_id, COUNT(*) as episodes_count, COALESCE(SUM(episodes.duration), 0) as duration, MIN(' . $completedAt . ') as first_completed_at, MAX(' . $completedAt . ') as last_completed_at')
            ->get()
            ->each(function ($row) use (&$watched) {
                $watched['anime'][$row->user_id][(int) $row->anime_id] = [
                    'count' => (int) $row->episodes_count,
                    'duration' => (int) $row->duration,
                    'first_completed_at' => $row->first_completed_at,
                    'last_completed_at' => $row->last_completed_at,
                ];
            });

        foreach ([
            'days' => 'DATE(' . $completedAt . ')',
            'weekdays' => 'WEEKDAY(' . $completedAt . ') + 1',
            'hours' => 'HOUR(' . $completedAt . ')',
        ] as $key => $expression) {
            $this->watchedEpisodesQuery($userIDs)
                ->groupByRaw('user_watched_episodes.user_id, ' . $expression)
                ->selectRaw('user_watched_episodes.user_id, ' . $expression . ' as bucket, COUNT(*) as episodes_count')
                ->get()
                ->each(function ($row) use (&$watched, $key) {
                    $watched[$key][$row->user_id][$row->bucket] = (int) $row->episodes_count;
                });
        }

        $this->watchedEpisodesQuery($userIDs)
            ->whereNotNull(UserWatchedEpisode::TABLE_NAME . '.provider_id')
            ->groupBy([UserWatchedEpisode::TABLE_NAME . '.user_id', UserWatchedEpisode::TABLE_NAME . '.provider_id'])
            ->selectRaw('user_watched_episodes.user_id, user_watched_episodes.provider_id, COUNT(*) as episodes_count')
            ->get()
            ->each(function ($row) use (&$watched) {
                $watched['providers'][$row->user_id][(int) $row->provider_id] = (int) $row->episodes_count;
            });

        return $watched;
    }

    /**
     * The episodes the given users completed in the recap period of anime tracked in it.
     *
     * @param array $userIDs
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function watchedEpisodesQuery(array $userIDs): \Illuminate\Database\Query\Builder
    {
        return UserWatchedEpisode::whereIn(UserWatchedEpisode::TABLE_NAME . '.user_id', $userIDs)
            ->completed()
            ->whereBetween(UserWatchedEpisode::TABLE_NAME . '.completed_at', [$this->startedAt, $this->endedAt])
            ->join(Episode::TABLE_NAME, UserWatchedEpisode::TABLE_NAME . '.episode_id', '=', Episode::TABLE_NAME . '.id')
            ->join(Season::TABLE_NAME, Episode::TABLE_NAME . '.season_id', '=', Season::TABLE_NAME . '.id')
            ->whereIn(Season::TABLE_NAME . '.anime_id', function ($subQuery) {
                $subQuery->select('trackable_id')
                    ->from(UserLibrary::TABLE_NAME)
                    ->whereColumn(UserLibrary::TABLE_NAME . '.user_id', '=', UserWatchedEpisode::TABLE_NAME . '.user_id')
                    ->whereNull(UserLibrary::TABLE_NAME . '.deleted_at')
                    ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', Anime::class)
                    ->whereIn(UserLibrary::TABLE_NAME . '.status', [UserLibraryStatus::InProgress, UserLibraryStatus::Completed, UserLibraryStatus::OnHold]);

                $this->whereLibraryInPeriod($subQuery, UserLibrary::TABLE_NAME);
            })
            ->toBase();
    }

    /**
     * The studios, cast and staff of the given titles.
     *
     * @param array $titles
     * @param array $watched
     *
     * @return array
     */
    protected function loadCredits(array $titles, array $watched): array
    {
        $titleIDs = array_map(fn ($measures) => array_keys($measures), $titles['measures']);
        $watchedAnimeIDs = [];

        foreach ($watched['anime'] as $userAnime) {
            foreach (array_keys($userAnime) as $animeID) {
                $watchedAnimeIDs[$animeID] = true;
            }
        }

        $credits = [
            'studios' => [],
            'staff' => [],
            'cast' => [],
        ];

        foreach ($titleIDs as $type => $ids) {
            foreach (array_chunk($ids, 1000) as $idsChunk) {
                MediaStudio::where('model_type', '=', $type)
                    ->whereIn('model_id', $idsChunk)
                    ->where('is_studio', '=', true)
                    ->toBase()
                    ->get(['model_id', 'studio_id'])
                    ->each(function ($mediaStudio) use (&$credits, $type) {
                        $credits['studios'][$type][$mediaStudio->model_id][$mediaStudio->studio_id] = true;
                    });

                MediaStaff::where('model_type', '=', $type)
                    ->whereIn('model_id', $idsChunk)
                    ->toBase()
                    ->get(['model_id', 'person_id', 'staff_role_id'])
                    ->each(function ($mediaStaff) use (&$credits, $type) {
                        $credits['staff'][$type][$mediaStaff->model_id][$mediaStaff->person_id] ??= $mediaStaff->staff_role_id === null ? null : (int) $mediaStaff->staff_role_id;
                    });
            }
        }

        foreach (array_chunk(array_keys($watchedAnimeIDs), 1000) as $animeIDsChunk) {
            AnimeCast::whereIn('anime_id', $animeIDsChunk)
                ->when($this->supportingCastRoleID, function ($query) {
                    $query->where('cast_role_id', '!=', $this->supportingCastRoleID);
                })
                ->toBase()
                ->get(['anime_id', 'character_id', 'person_id', 'language_id'])
                ->each(function ($animeCast) use (&$credits) {
                    $credits['cast'][$animeCast->anime_id][] = [
                        'character_id' => (int) $animeCast->character_id,
                        'person_id' => $animeCast->language_id == $this->japaneseLanguageID && $animeCast->person_id !== null ? (int) $animeCast->person_id : null,
                    ];
                });
        }

        return $credits;
    }

    /**
     * The activity counts of the given users in the recap period.
     *
     * @param array $userIDs
     *
     * @return array
     */
    protected function loadActivityCounts(array $userIDs): array
    {
        $period = [$this->startedAt, $this->endedAt];

        $ratings = MediaRating::withoutGlobalScope(MorphTvRatingScope::class)
            ->whereIn('user_id', $userIDs)
            ->whereIn('model_type', self::TRACKABLE_TYPES)
            ->whereBetween('created_at', $period)
            ->groupBy('user_id')
            ->toBase()
            ->selectRaw('user_id, COUNT(*) as ratings_count, AVG(rating) as average_rating')
            ->get()
            ->keyBy('user_id');

        $countPerUser = function ($query) {
            return $query->groupBy('user_id')
                ->toBase()
                ->selectRaw('user_id, COUNT(*) as aggregate')
                ->pluck('aggregate', 'user_id')
                ->all();
        };

        return [
            RecapStatType::RatingsGiven => $ratings->map(fn ($rating) => (int) $rating->ratings_count)->all(),
            RecapStatType::AverageRating => $ratings->map(fn ($rating) => (int) round($rating->average_rating * 100))->all(),
            RecapStatType::ReviewsWritten => $countPerUser(MediaRating::withoutGlobalScope(MorphTvRatingScope::class)
                ->whereIn('user_id', $userIDs)
                ->whereIn('model_type', self::TRACKABLE_TYPES)
                ->whereBetween('description_written_at', $period)),
            RecapStatType::TitlesCompleted => $countPerUser(UserLibrary::whereIn('user_id', $userIDs)
                ->where('status', '=', UserLibraryStatus::Completed)
                ->whereBetween('ended_at', $period)),
            RecapStatType::TitlesDropped => $countPerUser(UserLibrary::whereIn('user_id', $userIDs)
                ->where('status', '=', UserLibraryStatus::Dropped)
                ->whereBetween('updated_at', $period)),
            RecapStatType::TitlesAdded => $countPerUser(UserLibrary::whereIn('user_id', $userIDs)
                ->whereBetween('created_at', $period)),
            RecapStatType::FavoritesAdded => $countPerUser(UserFavorite::whereIn('user_id', $userIDs)
                ->whereIn('favorable_type', self::TRACKABLE_TYPES)
                ->whereBetween('created_at', $period)),
            RecapStatType::AchievementsEarned => $countPerUser(UserAchievement::whereIn('user_id', $userIDs)
                ->whereBetween('created_at', $period)),
        ];
    }

    /**
     * Generates the recaps of a user keyed by type.
     *
     * @param array $library
     * @param array $titles
     * @param array $ratings
     * @param array $watchedAnime
     * @param array $credits
     *
     * @return array
     */
    protected function generateUserRecaps(array $library, array $titles, array $ratings, array $watchedAnime, array $credits): array
    {
        $baseScore = array_fill_keys(self::TRACKABLE_TYPES, 0);
        $totalPartsDurations = array_fill_keys(self::TRACKABLE_TYPES, 0);
        $totalPartsCount = array_fill_keys(self::TRACKABLE_TYPES, 0);
        $totalSeriesCount = array_fill_keys(self::TRACKABLE_TYPES, 0);
        $topModels = array_fill_keys(self::TRACKABLE_TYPES, []);
        $partMeasures = array_fill_keys(self::TRACKABLE_TYPES, []);
        $trackedTitles = array_fill_keys(self::TRACKABLE_TYPES, []);
        $genresArray = [];
        $themesArray = [];

        // Populate genreCount, themeCount and totalDurations.
        foreach ($library as $entry) {
            $type = $entry['type'];

            if (!isset($totalSeriesCount[$type])) {
                continue;
            }

            $totalSeriesCount[$type]++;
            $measure = $titles['measures'][$type][$entry['id']] ?? null;

            if ($measure === null) {
                continue;
            }

            $totalPartsCount[$type] += $type === Manga::class ? $measure['chapter_count'] : 0;

            // Ignored titles don't count toward favorite genres and themes
            if ($entry['status'] != UserLibraryStatus::Ignored) {
                foreach ($titles['genres'][$type][$entry['id']] ?? [] as $genreID) {
                    $genresArray[$genreID] = ($genresArray[$genreID] ?? 0) + 1;
                }

                foreach ($titles['themes'][$type][$entry['id']] ?? [] as $themeID) {
                    $themesArray[$themeID] = ($themesArray[$themeID] ?? 0) + 1;
                }
            }

            $isCompleted = $entry['status'] == UserLibraryStatus::Completed;
            $duration = match ($type) {
                Manga::class => $measure['duration'] * $measure['page_count'],
                Game::class => $measure['duration'],
                default => 0,
            };

            // Determine progress duration
            if ($isCompleted) {
                $totalPartsDurations[$type] += $duration;
            }

            $partMeasures[$type][$entry['id']] = match ($type) {
                Manga::class => [
                    'count' => $measure['chapter_count'],
                    'duration' => $isCompleted ? $duration : 0,
                ],
                Game::class => [
                    'count' => 1,
                    'duration' => $isCompleted ? $duration : 0,
                ],
                default => [
                    'count' => 0,
                    'duration' => 0,
                ],
            };
        }

        foreach ($watchedAnime as $animeID => $watchedMeasure) {
            $partMeasures[Anime::class][$animeID] = [
                'count' => $watchedMeasure['count'],
                'duration' => $watchedMeasure['duration'],
            ];
            $totalPartsCount[Anime::class] += $watchedMeasure['count'];
            $totalPartsDurations[Anime::class] += $watchedMeasure['duration'];
        }

        $totalPartsCount[Game::class] = 1;

        // Find the genre and theme with the highest count
        arsort($genresArray);
        arsort($themesArray);
        $topGenres = array_slice(array_keys($genresArray), 0, self::ITEM_LIMIT);
        $topThemes = array_slice(array_keys($themesArray), 0, self::ITEM_LIMIT);

        foreach (self::TRACKABLE_TYPES as $type) {
            $typeRatings = array_column(array_filter($ratings, fn ($rating) => $rating['type'] === $type), 'rating');
            $averageUserRating = empty($typeRatings) ? null : array_sum($typeRatings) / count($typeRatings);
            $weightedAverageDuration = (max($totalPartsDurations[$type], 1) / max($totalPartsCount[$type], 1));

            // Combine average user rating and weighted duration into a score
            $baseScore[$type] = ($averageUserRating * 0.7) + ($weightedAverageDuration * 0.3);
        }

        // Populate top models
        foreach ($library as $entry) {
            $type = $entry['type'];

            if (
                !isset($titles['measures'][$type][$entry['id']]) ||
                $entry['status'] == UserLibraryStatus::Planning ||
                $entry['status'] == UserLibraryStatus::Dropped ||
                $entry['status'] == UserLibraryStatus::Interested ||
                $entry['status'] == UserLibraryStatus::Ignored
            ) {
                continue;
            }

            $trackedTitles[$type][] = $entry['id'];

            // Calculate a weighted completion score based on completion status
            $completionScore = 0;

            if ($entry['status'] === UserLibraryStatus::Completed) {
                $completionScore = 0.5;
            } else if ($entry['status'] === UserLibraryStatus::InProgress) {
                $completionScore = 0.3;
            } else if ($entry['status'] === UserLibraryStatus::Interested) {
                $completionScore = 0.15;
            } else if ($entry['status'] === UserLibraryStatus::OnHold) {
                $completionScore = 0.1;
            }

            $userRating = $ratings[$type . ':' . $entry['id']] ?? null;
            $completionScore += $userRating ? ($userRating['rating'] - 3) * 0.05 : 0;

            if ($userRating && $userRating['has_description']) {
                if ($userRating['rating'] >= 3) {
                    $completionScore += $userRating['rating'] * 10;
                } else {
                    $completionScore -= $userRating['rating'] * 10;
                }
            }

            // Combine base score with completion score
            $topModels[$type][$entry['id']] = $baseScore[$type] + $completionScore;
        }

        $recaps = [];

        foreach (self::TRACKABLE_TYPES as $type) {
            arsort($topModels[$type]);
            $modelIDs = array_slice(array_keys($topModels[$type]), 0, self::ITEM_LIMIT);

            if (empty($modelIDs)) {
                continue;
            }

            $recaps[$type] = [
                'total_series_count' => $totalSeriesCount[$type],
                'total_parts_count' => $totalPartsCount[$type],
                'total_parts_duration' => $totalPartsDurations[$type],
                'items' => array_map(function ($modelID) use ($type, $partMeasures) {
                    return [
                        'model_type' => $type,
                        'model_id' => $modelID,
                        'parts_count' => $partMeasures[$type][$modelID]['count'] ?? 0,
                        'parts_duration' => $partMeasures[$type][$modelID]['duration'] ?? 0,
                    ];
                }, $modelIDs),
            ];
        }

        foreach ([Genre::class => $topGenres, Theme::class => $topThemes] as $type => $modelIDs) {
            if (empty($modelIDs)) {
                continue;
            }

            $recaps[$type] = [
                'total_series_count' => 0,
                'total_parts_count' => 0,
                'total_parts_duration' => 0,
                'items' => array_map(function ($modelID) use ($type) {
                    return [
                        'model_type' => $type,
                        'model_id' => $modelID,
                        'parts_count' => 0,
                        'parts_duration' => 0,
                    ];
                }, $modelIDs),
            ];
        }

        return $recaps + $this->generateCreditRecaps($trackedTitles, $partMeasures, $credits);
    }

    /**
     * Generates the studio, character, voice and creator recaps of the given titles.
     *
     * @param array $trackedTitles
     * @param array $partMeasures
     * @param array $credits
     *
     * @return array
     */
    protected function generateCreditRecaps(array $trackedTitles, array $partMeasures, array $credits): array
    {
        $trackedAnimeIDs = array_flip($trackedTitles[Anime::class]);
        $studios = [];
        $characters = [];
        $voices = [];
        $creators = [];

        foreach ($trackedTitles[Anime::class] as $animeID) {
            $minutes = $partMeasures[Anime::class][$animeID]['duration'] ?? 0;

            foreach (array_keys($credits['studios'][Anime::class][$animeID] ?? []) as $studioID) {
                $studios[$studioID]['count'] = ($studios[$studioID]['count'] ?? 0) + 1;
                $studios[$studioID]['duration'] = ($studios[$studioID]['duration'] ?? 0) + $minutes;
            }
        }

        foreach ($partMeasures[Anime::class] as $animeID => $measure) {
            if (!isset($trackedAnimeIDs[$animeID])) {
                continue;
            }

            $countedCharacters = [];
            $countedVoices = [];

            foreach ($credits['cast'][$animeID] ?? [] as $castMember) {
                $characterID = $castMember['character_id'];

                if (!isset($countedCharacters[$characterID])) {
                    $countedCharacters[$characterID] = true;
                    $characters[$characterID]['count'] = ($characters[$characterID]['count'] ?? 0) + 1;
                    $characters[$characterID]['duration'] = ($characters[$characterID]['duration'] ?? 0) + $measure['duration'];
                }

                $personID = $castMember['person_id'];

                if ($personID === null) {
                    continue;
                }

                $voices[$personID]['characters'][$characterID] = true;
                $voices[$personID]['count'] = count($voices[$personID]['characters']);

                if (!isset($countedVoices[$personID])) {
                    $countedVoices[$personID] = true;
                    $voices[$personID]['duration'] = ($voices[$personID]['duration'] ?? 0) + $measure['duration'];
                }
            }
        }

        foreach ($trackedTitles as $type => $titleIDs) {
            foreach ($titleIDs as $titleID) {
                $duration = $partMeasures[$type][$titleID]['duration'] ?? 0;

                foreach ($credits['staff'][$type][$titleID] ?? [] as $personID => $staffRoleID) {
                    $creators[$personID]['count'] = ($creators[$personID]['count'] ?? 0) + 1;
                    $creators[$personID]['duration'] = ($creators[$personID]['duration'] ?? 0) + $duration;

                    if ($staffRoleID !== null && $duration >= ($creators[$personID]['related_duration'] ?? -1)) {
                        $creators[$personID]['related_duration'] = $duration;
                        $creators[$personID]['role_type'] = StaffRole::class;
                        $creators[$personID]['role_id'] = $staffRoleID;
                    }
                }
            }
        }

        $recaps = [];

        foreach ([
            Studio::class => [Studio::class, $studios],
            Character::class => [Character::class, $characters],
            Person::class => [Person::class, $voices],
            MediaStaff::class => [Person::class, $creators],
        ] as $type => [$modelType, $measures]) {
            if (empty($measures)) {
                continue;
            }

            uasort($measures, function (array $measure, array $otherMeasure) {
                return [$otherMeasure['count'], $otherMeasure['duration']] <=> [$measure['count'], $measure['duration']];
            });
            $measures = array_slice($measures, 0, $type === Studio::class ? self::ITEM_LIMIT : self::CREDIT_ITEM_LIMIT, true);

            $recaps[$type] = [
                'total_series_count' => count($measures),
                'total_parts_count' => 0,
                'total_parts_duration' => 0,
                'items' => array_map(function ($modelID) use ($modelType, $measures) {
                    return [
                        'model_type' => $modelType,
                        'model_id' => $modelID,
                        'parts_count' => $measures[$modelID]['count'],
                        'parts_duration' => $measures[$modelID]['duration'],
                        'related_model_type' => $measures[$modelID]['related_model_type'] ?? null,
                        'related_model_id' => $measures[$modelID]['related_model_id'] ?? null,
                        'role_type' => $measures[$modelID]['role_type'] ?? null,
                        'role_id' => $measures[$modelID]['role_id'] ?? null,
                    ];
                }, array_keys($measures)),
            ];
        }

        return $recaps;
    }

    /**
     * Generates the stat rows of the given user.
     *
     * @param int   $userID
     * @param array $watched
     * @param array $activityCounts
     *
     * @return array
     */
    protected function generateUserStats(int $userID, array $watched, array $activityCounts): array
    {
        $stats = [];
        $days = $watched['days'][$userID] ?? [];

        if (!empty($days)) {
            ksort($days);
            $weekdays = $watched['weekdays'][$userID] ?? [];
            $hours = $watched['hours'][$userID] ?? [];
            $binges = $days;
            arsort($weekdays);
            arsort($hours);
            arsort($binges);

            $stats[RecapStatType::BusiestWeekday] = ['value' => (int) array_key_first($weekdays)];
            $stats[RecapStatType::BusiestHour] = ['value' => (int) array_key_first($hours)];
            $stats[RecapStatType::BiggestBinge] = ['value' => reset($binges), 'occurred_at' => array_key_first($binges)];
            $stats[RecapStatType::LongestStreak] = $this->longestStreak(array_keys($days));
        }

        $watchedAnime = $watched['anime'][$userID] ?? [];

        if (!empty($watchedAnime)) {
            $firstAnimeID = array_key_first($watchedAnime);
            $lastAnimeID = $firstAnimeID;

            foreach ($watchedAnime as $animeID => $watchedMeasure) {
                if ($watchedMeasure['first_completed_at'] < $watchedAnime[$firstAnimeID]['first_completed_at']) {
                    $firstAnimeID = $animeID;
                }

                if ($watchedMeasure['last_completed_at'] > $watchedAnime[$lastAnimeID]['last_completed_at']) {
                    $lastAnimeID = $animeID;
                }
            }

            foreach ([
                RecapStatType::FirstTitle => [$firstAnimeID, 'first_completed_at'],
                RecapStatType::LastTitle => [$lastAnimeID, 'last_completed_at'],
            ] as $stat => [$animeID, $key]) {
                $stats[$stat] = [
                    'value' => $watchedAnime[$animeID]['count'],
                    'model_type' => Anime::class,
                    'model_id' => $animeID,
                    'occurred_at' => substr($watchedAnime[$animeID][$key], 0, 10),
                ];
            }
        }

        $providers = $watched['providers'][$userID] ?? [];

        if (!empty($providers)) {
            arsort($providers);
            $stats[RecapStatType::TopProvider] = [
                'value' => reset($providers),
                'model_type' => Provider::class,
                'model_id' => array_key_first($providers),
            ];
        }

        foreach ($activityCounts as $stat => $counts) {
            if (!empty($counts[$userID])) {
                $stats[$stat] = ['value' => (int) $counts[$userID]];
            }
        }

        $now = now();

        return array_map(function ($stat, array $values) use ($userID, $now) {
            return [
                'user_id' => $userID,
                'year' => $this->year,
                'month' => $this->recapMonth,
                'stat' => $stat,
                'value' => $values['value'],
                'model_type' => $values['model_type'] ?? null,
                'model_id' => $values['model_id'] ?? null,
                'occurred_at' => $values['occurred_at'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, array_keys($stats), $stats);
    }

    /**
     * The longest run of consecutive days in the given sorted days.
     *
     * @param array $days
     *
     * @return array
     */
    protected function longestStreak(array $days): array
    {
        $longest = 0;
        $longestEndedAt = null;
        $current = 0;
        $previousDay = null;

        foreach ($days as $day) {
            $date = Carbon::parse($day);
            $current = $previousDay?->copy()->addDay()->isSameDay($date) ? $current + 1 : 1;
            $previousDay = $date;

            if ($current > $longest) {
                $longest = $current;
                $longestEndedAt = $day;
            }
        }

        return [
            'value' => $longest,
            'occurred_at' => $longestEndedAt,
        ];
    }

    /**
     * Saves the recaps, items and stats of the given users in bulk.
     *
     * @param array $userIDs
     * @param array $recapRows
     * @param array $recapItems
     * @param array $statRows
     *
     * @return void
     * @throws Throwable
     */
    protected function saveChunk(array $userIDs, array $recapRows, array $recapItems, array $statRows): void
    {
        DB::transaction(function () use ($userIDs, $recapRows, $recapItems, $statRows) {
            foreach (array_chunk($recapRows, 1000) as $recapRowsChunk) {
                Recap::upsert(
                    $recapRowsChunk,
                    ['type', 'user_id', 'year', 'month'],
                    ['total_series_count', 'total_parts_count', 'total_parts_duration', 'updated_at']
                );
            }

            $recaps = Recap::whereIn('user_id', $userIDs)
                ->where([
                    ['year', '=', $this->year],
                    ['month', '=', $this->recapMonth],
                ])
                ->toBase()
                ->get(['id', 'user_id', 'type']);

            if ($recaps->isNotEmpty()) {
                RecapItem::whereIn('recap_id', $recaps->pluck('id'))
                    ->delete();
            }

            $staleRecapIDs = [];
            $itemRows = [];
            $now = now();

            foreach ($recaps as $recap) {
                $items = $recapItems[$recap->user_id . ':' . $recap->type] ?? null;

                if ($items === null) {
                    $staleRecapIDs[] = $recap->id;
                    continue;
                }

                foreach (array_values($items) as $index => $item) {
                    $itemRows[] = $item + [
                        'related_model_type' => null,
                        'related_model_id' => null,
                        'role_type' => null,
                        'role_id' => null,
                        'recap_id' => $recap->id,
                        'position' => $index + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($staleRecapIDs)) {
                Recap::whereIn('id', $staleRecapIDs)
                    ->delete();
            }

            foreach (array_chunk($itemRows, 1000) as $itemRowsChunk) {
                RecapItem::insert($itemRowsChunk);
            }

            RecapStat::whereIn('user_id', $userIDs)
                ->where([
                    ['year', '=', $this->year],
                    ['month', '=', $this->recapMonth],
                ])
                ->delete();

            foreach (array_chunk($statRows, 1000) as $statRowsChunk) {
                RecapStat::insert($statRowsChunk);
            }
        });
    }

    /**
     * Deletes the period's recaps and stats of users without activity in it.
     *
     * @return void
     */
    protected function deleteInactiveRecaps(): void
    {
        $activeUserIDs = $this->whereActive(User::withoutGlobalScopes()->select('id'));

        $deletedRecapsCount = Recap::where([
            ['year', '=', $this->year],
            ['month', '=', $this->recapMonth],
        ])
            ->whereNotIn('user_id', $activeUserIDs)
            ->delete();

        $deletedStatsCount = RecapStat::where([
            ['year', '=', $this->year],
            ['month', '=', $this->recapMonth],
        ])
            ->whereNotIn('user_id', $activeUserIDs)
            ->delete();

        $this->line('Deleted ' . $deletedRecapsCount . ' recaps and ' . $deletedStatsCount . ' stats of inactive users');
    }

    /**
     * Ranks every recap of the period by series count.
     *
     * @return void
     */
    protected function calculateTopPercentiles(): void
    {
        foreach (self::TRACKABLE_TYPES as $type) {
            $rankedRecaps = Recap::select([
                'id',
                DB::raw('CEIL(ROW_NUMBER() OVER (ORDER BY total_series_count DESC, id) * 10000 / COUNT(*) OVER ()) / 100 AS percentile'),
            ])
                ->where([
                    ['type', '=', $type],
                    ['year', '=', $this->year],
                    ['month', '=', $this->recapMonth],
                ]);

            $updatedCount = Recap::joinSub($rankedRecaps, 'ranked_recaps', function ($join) {
                $join->on(Recap::TABLE_NAME . '.id', '=', 'ranked_recaps.id');
            })
                ->toBase()
                ->update([
                    Recap::TABLE_NAME . '.top_percentile' => DB::raw('ranked_recaps.percentile'),
                ]);

            $this->line('Ranked ' . $updatedCount . ' ' . class_basename($type) . ' recaps');
        }
    }
}
