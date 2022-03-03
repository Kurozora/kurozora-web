<?php

namespace App\Console\Commands\Generators;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use App\Scopes\TvRatingScope;
use Carbon\Carbon;
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
        $totalSeasonsAdded = 0;
        $totalEpisodesAdded = 0;
        Anime::withoutGlobalScope(new TvRatingScope)
            ->where([
                ['first_aired', '!=', null],
                ['last_aired', '!=', null],
                ['season_count', '=', 1],
                ['episode_count', '<=', 13],
                ['status_id', '=', 4],
            ])
            ->chunk(20000, function (Collection $animes) use (&$totalSeasonsAdded, &$totalEpisodesAdded) {
                /** @var Anime[] $animes */
                foreach ($animes as $anime) {
                    echo '[] generating for anime: ' . $anime->id . PHP_EOL;
                    /** @var Season|null $season */
                    $season = null;
                    /** @var Episode[] $episodes */
                    $episodes = [];

                    // Generate season
                    if ($anime->seasons()->count() === 0) {
                        /** @var Season $season */
                        $season = $anime->seasons()->create([
                            'number' => 1,
                            'title' => 'Season 1',
                            'synopsis' => $anime->synopsis,
                            'ja' => [
                                'title' => 'シーズン1',
                                'synopsis' => null,
                            ],
                            'first_aired' => Carbon::createFromFormat('Y-m-d H:i:s', $anime->first_aired->toDateString() . ' ' . $anime->air_time, 'Asia/Tokyo')->setTimezone('UTC'),
                            'last_aired' => Carbon::createFromFormat('Y-m-d H:i:s', $anime->last_aired->toDateString() . ' ' . $anime->air_time, 'Asia/Tokyo')->setTimezone('UTC'),
                        ]);

                        echo '[][] season id: ' . $season->id . PHP_EOL;
                        $totalSeasonsAdded += 1;
                    }

                    // Generate episodes
                    if (!empty($season)) {
                        if ($anime->episodes()->count() != $anime->episode_count) {
                            $sameDayRelease = $anime->first_aired->equalTo($anime->last_aired) ?? false;

                            foreach (range(1, $anime->episode_count) as $count) {
                                /** @var Episode $episode */
                                $episode = $season->episodes()->create([
                                    'number' => $count,
                                    'number_total' => $count,
                                    'title' => 'Episode ' . $count,
                                    'synopsis' => null,
                                    'ja' => [
                                        'title' => '第' . $count . '話',
                                        'synopsis' => null,
                                    ],
                                    'duration' => $anime->duration,
                                    'first_aired' => $sameDayRelease ? $season->first_aired->setTimezone('UTC') : $season->first_aired->addWeeks($count - 1)->setTimezone('UTC'),
                                    'is_filler' => false,
                                    'verified' => false,
                                ]);
                                $episodes[] = $episode;

                                echo __('[][][] episode id: ') . $episode->id . PHP_EOL;
                                $totalEpisodesAdded += 1;
                            }
                        }
                    }
                    echo __('[][][] episodes count: ') . count($episodes) . PHP_EOL;
                    echo '----------------------------------' . PHP_EOL;
                }
            });

        echo '🌤 total seasons added: ' . $totalSeasonsAdded . PHP_EOL;
        echo '📺 total episodes added: ' . $totalEpisodesAdded . PHP_EOL;

        return Command::SUCCESS;
    }
}
