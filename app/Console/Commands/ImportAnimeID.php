<?php

namespace App\Console\Commands;

use App\Models\Anime;
use Illuminate\Console\Command;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\JsonMachine;

class ImportAnimeID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime ids for different services.';

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
        $file = storage_path('app/anime-offline-database.json');
        $fileSize = filesize($file);
        $animes = JsonMachine::fromFile($file, '/data', new ExtJsonDecoder);

        $progressBar = $this->output->createProgressBar($fileSize);
        $progressBar->start();

        foreach ($animes as $data) {
            $sources = $this->filterSources($data->sources);

            if (array_key_exists('mal_id', $sources)) {
                $anime = Anime::firstWhere([
                    ['mal_id', $sources['mal_id']],
                ]);

                if (!empty($anime)) {
                    $anime->update($sources);
                }
            }

            $progress = $animes->getPosition();
            $progressBar->setProgress($progress);
        }

        $progressBar->finish();
        return 1;
    }

    /**
     * Returns an array of the sources as key_id => id.
     *
     * @param array $sources
     * @return array
     */
    protected function filterSources(array $sources): array
    {
        if (empty($sources)) {
            return $sources;
        }
        $regexSearch = [
            'anidb_id' => '#https:\/\/anidb.net\/anime\/(\w+)$#i',
            'anilist_id' => '#https:\/\/anilist.co\/anime\/(\w+)$#i',
            'kitsu_id' => '#https:\/\/kitsu.io\/anime\/(\w+)$#i',
            'mal_id' => '#https:\/\/myanimelist.net\/anime/(\w+)$#i',
            'notify_id' => '#https:\/\/notify.moe\/anime/(\w+)$#i',
        ];
        $matchedSources = [];

        foreach ($regexSearch as $key => $regex) {
            $matchArray = preg_grep($regex, $sources);

            if (!empty($matchArray)) {
                foreach ($matchArray as $item) {
                    $matchCount = preg_match($regex, $item, $match);

                    if ($matchCount) {
                        $matchedSources[$key] = $match[1];
                    }
                }
            }
        }

        return $matchedSources;
    }
}
