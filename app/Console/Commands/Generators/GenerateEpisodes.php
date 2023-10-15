<?php

namespace App\Console\Commands\Generators;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
        DB::disableQueryLog();

        $totalSeasonsAdded = 0;
        $totalEpisodesAdded = 0;

        Anime::withoutGlobalScopes()
            ->where([
                ['started_at', '!=', null],
                ['season_count', '=', 1],
                ['episode_count', '!=', null]
            ])
            ->with(['translations'])
            ->chunkById(500, function (Collection $animes) use (&$totalSeasonsAdded, &$totalEpisodesAdded) {
                $animes->each(function (Anime $anime) use (&$totalSeasonsAdded, &$totalEpisodesAdded) {
                    echo '[] generating for anime: ' . $anime->id . PHP_EOL;
                    /** @var Episode[] $episodes */
                    $episodes = [];

                    // Generate season
                    /** @var Season $season */
                    $season = $anime->seasons()->withoutGlobalScopes()
                        ->firstOrCreate([
                            'number' => 1
                        ], [
                            'title' => 'Season 1',
                            'synopsis' => $anime->synopsis,
                            'ja' => [
                                'title' => 'シーズン1',
                                'synopsis' => null,
                            ],
                            'started_at' => Carbon::createFromFormat('Y-m-d H:i:s', $anime->started_at->toDateString() . ' ' . $anime->air_time, 'Asia/Tokyo')->setTimezone('UTC'),
                            'ended_at' => $anime->ended_at ? Carbon::createFromFormat('Y-m-d H:i:s', $anime->ended_at->toDateString() . ' ' . $anime->air_time, 'Asia/Tokyo')->setTimezone('UTC') : null,
                        ]);

                    echo '[][] season id: ' . $season->id . PHP_EOL;
                    $totalSeasonsAdded += 1;

                    // Generate episodes
                    if ($anime->episodes()->withoutGlobalScopes()->count() != $anime->episode_count) {
                        $sameDayRelease = $anime->started_at->equalTo($anime->ended_at) ?? false;

                        foreach (range(1, $anime->episode_count) as $count) {
                            $startedAt = $sameDayRelease ? $season->started_at->setTimezone('UTC') : $season->started_at->addWeeks($count - 1)->setTimezone('UTC');

                            $episode = $season->episodes()
                                ->withoutGlobalScopes()
                                ->firstOrCreate([
                                    'number' => $count,
                                    'number_total' => $count,
                                ], [
                                    'title' => 'Episode ' . $count,
                                    'synopsis' => null,
                                    'ja' => [
                                        'title' => '第' . $count . '話',
                                        'synopsis' => null,
                                    ],
                                    'duration' => $anime->duration,
                                    'is_filler' => false,
                                    'is_verified' => false,
                                    'is_premiere' => $count == 1,
                                    'is_finale' => $count == $anime->episode_count,
                                    'started_at' => $startedAt,
                                    'ended_at' => $startedAt->addSeconds($anime->duration),
                                ]);
                            $episodes[] = $episode;

                            echo __('[][][] episode id: ') . $episode->id . PHP_EOL;
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

                   echo __('[][][] episodes count: ') . count($episodes) . PHP_EOL;
                    echo '----------------------------------' . PHP_EOL;
                });
            });

        echo '🌤 total seasons added: ' . $totalSeasonsAdded . PHP_EOL;
        echo '📺 total episodes added: ' . $totalEpisodesAdded . PHP_EOL;

        DB::enableQueryLog();
        return Command::SUCCESS;
    }
}
