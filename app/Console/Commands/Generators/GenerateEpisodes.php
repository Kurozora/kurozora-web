<?php

namespace App\Console\Commands\Generators;

use App\Models\Anime;
use App\Services\EpisodeMaterializationService;
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
     * Execute the console command.
     *
     * @param EpisodeMaterializationService $materializer
     *
     * @return int
     */
    public function handle(EpisodeMaterializationService $materializer): int
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
            ->chunkById(500, function (Collection $animes) use (&$totalSeasonsAdded, &$totalEpisodesAdded, $materializer) {
                $animes->each(function (Anime $anime) use (&$totalSeasonsAdded, &$totalEpisodesAdded, $materializer) {
                    echo '[] generating for anime: ' . $anime->id . PHP_EOL;

                    $created = $materializer->materialize($anime);

                    if ($created > 0) {
                        $totalSeasonsAdded += 1;
                        $totalEpisodesAdded += $created;
                    }

                    echo '[][][] episodes count: ' . $created . PHP_EOL;
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
