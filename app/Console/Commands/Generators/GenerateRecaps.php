<?php

namespace App\Console\Commands\Generators;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Recap;
use App\Models\RecapItem;
use App\Models\Season;
use App\Models\Theme;
use App\Models\User;
use App\Models\UserLibrary;
use App\Models\UserWatchedEpisode;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Execute the console command.
     *
     * @return int
     * @throws Throwable
     */
    public function handle(): int
    {
        $user = $this->argument('userID');
        $year = $this->argument('year') ?? now()->year;
        $month = $this->argument('month');

        $startMonth = sprintf('%02d', (int) ($month ?? '1'));
        $endMonth = $month ?: '12';
        $startedAt = Carbon::createFromDate($year, $startMonth, 1)
            ->toDateString();
        $endedAt = Carbon::createFromDate($year, $endMonth, 1)
            ->endOfMonth()
            ->toDateString();

        $types = collect([
            Anime::class,
            Manga::class,
            Game::class,
            Genre::class,
            Theme::class,
        ]);

        DB::disableQueryLog();
        User::withoutGlobalScopes()
            ->when($user != 'all', function (Builder $query) use ($user) {
                $query->where('id', '=', $user);
            })
            ->with([
                'library' => function ($query) use ($startedAt, $endedAt, $year) {
                    $query->where([
                        ['started_at', '>=', $startedAt],
                        ['started_at', '<=', $endedAt],
                    ])
                        ->orWhere([
                            ['started_at', '>=', $startedAt],
                            ['ended_at', '<=', $endedAt],
                        ])
                        ->with([
                            'trackable.genres',
                            'trackable.themes'
                        ]);
                },
                'user_watched_episodes' => function (HasMany $query) use ($startedAt, $endedAt, $year) {
                    $query->where([
                        [UserWatchedEpisode::TABLE_NAME . '.created_at', '>=', $startedAt],
                        [UserWatchedEpisode::TABLE_NAME . '.created_at', '<=', $endedAt],
                    ])
                        ->join(Episode::TABLE_NAME, UserWatchedEpisode::TABLE_NAME . '.episode_id', '=', Episode::TABLE_NAME . '.id')
                        ->join(Season::TABLE_NAME, Episode::TABLE_NAME . '.season_id', '=', Season::TABLE_NAME . '.id')
                        ->join(Anime::TABLE_NAME, Season::TABLE_NAME . '.anime_id', '=', Anime::TABLE_NAME . '.id')
                        ->whereIn(Anime::TABLE_NAME . '.id', function ($subQuery) use ($startedAt, $endedAt, $year) {
                            $subQuery->select('trackable_id')
                                ->from(UserLibrary::TABLE_NAME)
                                ->where([
                                    [UserLibrary::TABLE_NAME . '.started_at', '>=', $startedAt],
                                    [UserLibrary::TABLE_NAME . '.started_at', '<=', $endedAt],
                                ])
                                ->orWhere([
                                    [UserLibrary::TABLE_NAME . '.started_at', '>=', $startedAt],
                                    [UserLibrary::TABLE_NAME . '.ended_at', '<=', $endedAt],
                                ])
                                ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', Anime::class)
                                ->whereIn(UserLibrary::TABLE_NAME . '.status', [UserLibraryStatus::InProgress, UserLibraryStatus::Completed, UserLibraryStatus::OnHold]);
                        })
                        ->select(UserWatchedEpisode::TABLE_NAME . '.*')
                        ->with(['episode']);
                },
            ])
            ->chunkById(10, function (Collection $users) use ($endMonth, $year, $types) {
                $users->each(function (User $user) use ($types, $endMonth, $year) {
                    $baseScore = [
                        Anime::class => 0,
                        Manga::class => 0,
                        Game::class => 0,
                    ];
                    $totalPartsDurations = [
                        Anime::class => 0,
                        Manga::class => 0,
                        Game::class => 0,
                    ];
                    $totalPartsCount = [
                        Anime::class => 0,
                        Manga::class => 0,
                        Game::class => 0,
                    ];
                    $topModels = [
                        Anime::class => collect(),
                        Manga::class => collect(),
                        Game::class => collect(),
                    ];
                    $genresArray = [];
                    $themesArray = [];

                    // Populate genreCount, themeCount and totalDurations.
                    $user->library->each(function (UserLibrary $userLibrary) use ($user, &$totalPartsDurations, &$genresArray, &$themesArray) {
                        $model = $userLibrary->trackable;
                        $this->line('Populating count for: ' . $userLibrary->trackable_type);

                        if ($model == null) {
                            $this->info('empty model for: ' . $userLibrary->id);
                            return;
                        }

                        // Anything that's ignored means the user doesn't like
                        // them. These can mostly share the same genre. We ignore
                        // them, so they don't pollute the user's liked genres
                        // and themes.
                        if ($userLibrary->status != UserLibraryStatus::Ignored) {
                            // Determine favorite genre
                            $this->line('Counting genres');
                            $model->genres->each(function ($genre) use (&$genresArray) {
                                $genresArray[$genre->id] = isset($genresArray[$genre->id]) ? $genresArray[$genre->id] + 1 : 1;
                            });

                            // Determine favorite genre
                            $this->line('Counting themes');
                            $model->themes->each(function ($theme) use (&$themesArray) {
                                $themesArray[$theme->id] = isset($themesArray[$theme->id]) ? $themesArray[$theme->id] + 1 : 1;
                            });
                        }

                        // Determine progress duration
                        $this->line('Counting progress');
                        switch ($userLibrary->status) {
                            case UserLibraryStatus::Completed:
                                $totalPartsDurations[$userLibrary->trackable_type] += match ($userLibrary->trackable_type) {
                                    Manga::class => $model->duration * $model->page_count,
                                    Game::class => $model->duration,
                                    default => 0
                                };
                                break;
                            case UserLibraryStatus::Planning:
                            case UserLibraryStatus::Dropped:
                            case UserLibraryStatus::Ignored:
                                // Explicitly ignore those, as the user either hasn't
                                // started watching them or doesn't like them.
                                break;
                            default:
                                break;
                        }
                    });

                    // Find the genre with the highest count (favorite genre)
                    $topGenres = collect($genresArray)
                        ->sortDesc()
                        ->keys()
                        ->take(15);

                    // Find the theme with the highest count (favorite theme)
                    $topThemes = collect($themesArray)
                        ->sortDesc()
                        ->keys()
                        ->take(15);

                    // Get base score per type
                    $types->each(function ($type) use ($user, &$totalPartsDurations, &$totalPartsCount, &$baseScore) {
                        $this->line('Counting base score for: ' . $type);
                        switch ($type) {
                            case Anime::class:
                                // Sum total parts duration
                                $totalEpisodesDuration = $user->user_watched_episodes
                                    ->sum('episode.duration');
                                $totalPartsDurations[$type] = $totalEpisodesDuration;

                                // Get total completed parts count
                                $totalPartsCount[$type] = $user->user_watched_episodes->count();
                                break;
                            case Manga::class:
                                $totalPartsCount[$type] = $user->library
                                    ->where('trackable_type', '=', $type)
                                    ->sum('trackable.chapter_count');
                                break;
                            case Game::class:
                                $totalPartsCount[$type] = 1;
                                break;
                            default:
                                return;
                        }

                        $this->line('Counting average user rating: ' . $type);
                        $averageUserRating = MediaRating::where('user_id', '=', $user->id)
                            ->where('model_type', '=', $type)
                            ->avg('rating');
                        $weightedAverageDuration = (max($totalPartsDurations[$type], 1) / max($totalPartsCount[$type], 1));

                        // Combine average user rating and weighted duration into a score
                        $baseScore[$type] = ($averageUserRating * 0.7) + ($weightedAverageDuration * 0.3);
                    });

                    // Populate top models
                    $user->library->each(function (UserLibrary $userLibrary) use ($baseScore, $user, &$topModels) {
                        $this->line('Populating top models for: ' . $userLibrary->trackable_type);

                        if (
                            $userLibrary->status == UserLibraryStatus::Planning ||
                            $userLibrary->status == UserLibraryStatus::Dropped ||
                            $userLibrary->status == UserLibraryStatus::Ignored
                        ) {
                            return;
                        }

                        // Calculate a weighted completion score based on completion status
                        $completionScore = 0;

                        if ($userLibrary->status === UserLibraryStatus::Completed) {
                            $completionScore = 0.5;
                        } elseif ($userLibrary->status === UserLibraryStatus::InProgress) {
                            $completionScore = 0.3;
                        } elseif ($userLibrary->status === UserLibraryStatus::OnHold) {
                            $completionScore = 0.1;
                        }

                        // Add weight based on the user's rating
                        $this->line('Populating average rating: ' . $userLibrary->trackable_type);
                        $userRating = MediaRating::where('user_id', '=', $user->id)
                            ->where('model_type', '=', $userLibrary->trackable_type)
                            ->where('model_id', '=', $userLibrary->trackable_id)
                            ->first(['rating', 'description']);

                        // Combine base score with completion score
                        // $userRating: This is the user's rating for a particular media item. It's on a scale from 1 to 5.
                        // ($userRating - 3): This part of the expression shifts the scale so that a rating of 3 becomes the center. The idea is to give positive weight for ratings above 3 and negative weight for ratings below 3. This ensures that higher-rated items contribute positively to the score.
                        // * 0.05: This part scales the result to be between 0 and 0.2. By multiplying the shifted rating difference by 0.05, we're ensuring that the weight remains within this range. We can adjust this multiplier based on the desired impact of the rating on the overall score.
                        // : 0: This is a ternary operator, and it ensures that if $userRating is not set (i.e., the user hasn't rated the item), the weight is set to 0.
                        $ratingWeight = $userRating ? ($userRating->rating - 3) * 0.05 : 0; // Scale rating to be between 0 and 0.2
                        $completionScore += $ratingWeight;

                        if (!empty($userRating->description)) {
                            if ($userRating->rating >= 3) {
                                $completionScore += $userRating->rating * 10;
                            } else {
                                $completionScore -= $userRating->rating * 10;
                            }
                        }

                        // Combine base score with completion score
                        $combinedScore = $baseScore[$userLibrary->trackable_type] + $completionScore;

                        $topModels[$userLibrary->trackable_type]->put($userLibrary->trackable_id, $combinedScore);
                    });

                    // Sort
                    $topModels = collect($topModels)
                        ->map(function ($topModel) {
                            return $topModel->sortDesc()
                                ->keys()
                                ->take(15);
                        });

                    // Save Re:Cap results
                    $types->each(function($type) use ($topModels, $topGenres, $topThemes, $totalPartsCount, $totalPartsDurations, $endMonth, $year, $user) {
                        $this->line('Saving recap for: ' . $type);
                        switch ($type) {
                            case Genre::class:
                                if ($topGenres->isEmpty()) {
                                    return;
                                }

                                $recap = Recap::updateOrCreate([
                                    'user_id' => $user->id,
                                    'year' => $year,
                                    'month' => $endMonth,
                                    'type' => $type
                                ]);

                                DB::transaction(function () use ($type, $recap, $topGenres) {
                                    $recap->recapItems()
                                        ->forceDelete();

                                    $topGenres->each(function ($favoriteGenre) use ($recap, $type) {
                                        RecapItem::create([
                                            'recap_id' => $recap->id,
                                            'model_type' => $type,
                                            'model_id' => $favoriteGenre
                                        ]);
                                    });
                                });
                                break;
                            case Theme::class:
                                if ($topThemes->isEmpty()) {
                                    return;
                                }

                                $recap = Recap::updateOrCreate([
                                    'user_id' => $user->id,
                                    'year' => $year,
                                    'month' => $endMonth,
                                    'type' => $type
                                ]);

                                DB::transaction(function () use ($type, $recap, $topThemes) {
                                    $recap->recapItems()
                                        ->forceDelete();

                                    $topThemes->each(function ($favoriteTheme) use ($recap, $type) {
                                        RecapItem::create([
                                            'recap_id' => $recap->id,
                                            'model_type' => $type,
                                            'model_id' => $favoriteTheme
                                        ]);
                                    });
                                });
                                break;
                            default:
                                if (collect($topModels->get($type))->isEmpty()) {
                                    return;
                                }

                                $recap = Recap::updateOrCreate([
                                    'user_id' => $user->id,
                                    'year' => $year,
                                    'month' => $endMonth,
                                    'type' => $type
                                ], [
                                    'total_series_count' => $user->library
                                        ->where('trackable_type', '=', $type)
                                        ->count(),
                                    'total_parts_count' => $totalPartsCount[$type] ?? 0,
                                    'total_parts_duration' => $totalPartsDurations[$type] ?? 0
                                ]);

                                DB::transaction(function () use ($type, $recap, $topModels) {
                                    $recap->recapItems()
                                        ->forceDelete();

                                    collect($topModels->get($type))
                                        ->each(function ($topModelItem) use ($recap, $type) {
                                            RecapItem::create([
                                                'recap_id' => $recap->id,
                                                'model_type' => $type,
                                                'model_id' => $topModelItem
                                            ]);
                                        });
                                });
                        }
                    });
                });
            });

        // Calculate top percentile per type
        $types->each(function ($type) use ($month, $year, $user) {
            $this->line('Calculating top percentile for: ' . $type);

            if ($type == Genre::class || $type == Theme::class) {
                return;
            }

            $chunk = 500;
            $offset = 0;
            $totalRecaps =  Recap::withoutGlobalScopes()
                ->where('type', '=', $type)
                ->count();

            $this->line('Total recaps: ' . $totalRecaps);

            Recap::withoutGlobalScopes()
                ->when($user != 'all', function (Builder $query) use ($user) {
                    $query->where('user_id', '=', $user);
                })
                ->where([
                    ['type', '=', $type],
                    ['month', '=', $month],
                    ['year', '=', $year]
                ])
                ->orderBy('total_series_count', 'desc')
                ->chunkById($chunk, function (Collection $recaps) use ($totalRecaps, &$offset) {
                    DB::transaction(function () use (&$offset, $totalRecaps, $recaps) {
                        $recaps->each(function (Recap $recap) use (&$offset, $totalRecaps) {
                            // Calculate the percentile based on the rank
                            $percentile = ($offset + 1) / $totalRecaps * 100;

                            $recap->update([
                                'top_percentile' => $percentile
                            ]);

                            $offset++;
                        });

                        $this->line('Calculated up to recap: ' . $recaps->last()->id);
                    });
                });
        });
        DB::enableQueryLog();

        return Command::SUCCESS;
    }
}
