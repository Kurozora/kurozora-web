<?php

namespace App\Console\Commands\Generators;

use App\Enums\EpisodeFillerKind;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

class GenerateEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:episodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the episodes.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $totalSeasonsAdded = 0;
        $totalEpisodesAdded = 0;

        Anime::withoutGlobalScopes()
            ->where('animes.episode_count', '!=', 0)
            ->where('animes.season_count', '=', 1)
            ->where('animes.started_at', '!=', null)
            ->whereDoesntHave('episodes')
            ->with(['translations'])
            ->chunkById(500, function (Collection $animes) use (&$totalSeasonsAdded, &$totalEpisodesAdded) {
                $animes->each(function (Anime $anime) use (&$totalSeasonsAdded, &$totalEpisodesAdded) {
                    echo '[] generating for anime: ' . $anime->id . PHP_EOL;
                    /** @var Episode[] $episodes */
                    $episodes = [];
                    $airTime = $anime->air_time ?? '09:00:00';

                    // Generate season
                    /** @var Season $season */
                    $season = $anime->seasons()->withoutGlobalScopes()
                        ->firstWhere([
                            'number' => 1,
                        ]);

                    if (empty($season)) {
                        $season = $anime->seasons()->withoutGlobalScopes()
                            ->create([
                                'tv_rating_id' => $anime->tv_rating_id,
                                'tvdb_season' => 1,
                                'tvdb_episode_offset' => 0,
                                'is_nsfw' => $anime->is_nsfw,
                                'number' => 1,
                                'title' => 'Season 1',
                                'synopsis' => $anime->synopsis,
                                'ja' => [
                                    'title' => 'シーズン1',
                                    'synopsis' => null,
                                ],
                                'started_at' => Carbon::createFromFormat('Y-m-d H:i:s', $anime->started_at->toDateString() . ' ' . $airTime, 'Asia/Tokyo')->setTimezone('UTC'),
                                'ended_at' => $anime->ended_at ? Carbon::createFromFormat('Y-m-d H:i:s', $anime->ended_at->toDateString() . ' ' . $airTime, 'Asia/Tokyo')->setTimezone('UTC') : null,
                            ]);
                    } else {
                        $season->update([
                            'tv_rating_id' => $anime->tv_rating_id,
                            'is_nsfw' => $anime->is_nsfw,
                            'started_at' => Carbon::createFromFormat('Y-m-d H:i:s', $anime->started_at->toDateString() . ' ' . $airTime, 'Asia/Tokyo')->setTimezone('UTC'),
                            'ended_at' => $anime->ended_at ? Carbon::createFromFormat('Y-m-d H:i:s', $anime->ended_at->toDateString() . ' ' . $airTime, 'Asia/Tokyo')->setTimezone('UTC') : null,
                        ]);
                    }

                    echo '[][] season id: ' . $season->id . PHP_EOL;
                    $totalSeasonsAdded += 1;

                    // Generate episodes
                    $sameDayRelease = $anime->started_at == $anime->ended_at;

                    if ($anime->episode_count) {
                        foreach (range(1, $anime->episode_count) as $count) {
                            $seasonStartedAt = $season->started_at->copy();
                            $startedAt = $sameDayRelease ? $seasonStartedAt->setTimezone('UTC') : $seasonStartedAt->addWeeks($count - 1)->setTimezone('UTC');
                            $endedAt = ($startedAt->copy())
                                ->addSeconds($anime->duration);

                            $episode = $season->episodes()
                                ->withoutGlobalScopes()
                                ->firstWhere([
                                    'number' => $count,
                                    'number_total' => $count,
                                ]);

                            if (empty($episode)) {
                                $episode = $season->episodes()
                                    ->withoutGlobalScopes()
                                    ->create([
                                        'tv_rating_id' => $anime->tv_rating_id,
                                        'is_nsfw' => $anime->is_nsfw,
                                        'number' => $count,
                                        'number_total' => $count,
                                        'title' => 'Episode ' . $count,
                                        'synopsis' => null,
                                        'ja' => [
                                            'title' => '第' . $count . '話',
                                            'synopsis' => null,
                                        ],
                                        'duration' => $anime->duration,
                                        'filler_kind' => EpisodeFillerKind::AnimeCanon,
                                        'is_verified' => false,
                                        'is_premiere' => $count == 1,
                                        'is_finale' => $count == $anime->episode_count,
                                        'started_at' => $startedAt,
                                        'ended_at' => $endedAt,
                                    ]);
                            } else {
                                $episode->update([
                                    'tv_rating_id' => $anime->tv_rating_id,
                                    'is_nsfw' => $anime->is_nsfw,
                                    'duration' => $anime->duration,
                                    'is_premiere' => $count == 1,
                                    'is_finale' => $count == $anime->episode_count,
                                    'started_at' => $startedAt,
                                    'ended_at' => $endedAt,
                                ]);
                            }

                            $episodes[] = $episode;

                            echo '[][][] episode id: ' . $episode->id . PHP_EOL;
                            $totalEpisodesAdded += 1;
                        }

                        foreach ($episodes as $key => $episode) {
                            $nextEpisode = null;
                            $previousEpisode = null;

                            if ($key != count($episodes) - 1) {
                                $nextEpisode = $episodes[$key + 1]->id;
                            }

                            if ($key != 0) {
                                $previousEpisode = $episodes[$key - 1]->id;
                            }

                            $episode->update([
                                'next_episode_id' => $nextEpisode,
                                'previous_episode_id' => $previousEpisode,
                            ]);
                        }
                    }

                    $anime->updateQuietly([
                        'season_count' => 1,
                    ]);

                    echo '[][][] episodes count: ' . count($episodes) . PHP_EOL;
                    echo '----------------------------------' . PHP_EOL;
                });
            });

        echo '🌤 total seasons added: ' . $totalSeasonsAdded . PHP_EOL;
        echo '📺 total episodes added: ' . $totalEpisodesAdded . PHP_EOL;

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
