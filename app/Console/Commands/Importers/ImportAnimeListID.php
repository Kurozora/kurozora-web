<?php

namespace App\Console\Commands\Importers;

use App\Models\Anime;
use Http;
use Illuminate\Console\Command;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

class ImportAnimeListID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime_list_ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime ids from anime-list.';

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
     * @throws InvalidArgumentException
     */
    public function handle(): int
    {
        $file = storage_path('app/anime-list-full.json');
        $fileSize = filesize($file);
        $animes = Items::fromFile($file, [
            'pointer' => '',
            'decoder' => new ExtJsonDecoder,
            'debug' => true
        ]);

        $progressBar = $this->output->createProgressBar($fileSize);
        $progressBar->start();

        foreach ($animes as $data) {
//            if ($animes->getPosition() < 5268782) {
//                continue;
//            }

            $sources = $this->filterSources($data);

            if (array_key_exists('mal_id', $sources)) {
                $anime = Anime::withoutGlobalScopes()
                    ->where('mal_id', '=', $sources['mal_id'])
//                        ->whereDate('updated_at', '<', today())
                    ->first();

                if (!empty($anime)) {
                    if (!empty($anime->kitsu_id)) {
                        unset($sources['kitsu_id']);
                    }

                    $anime->update($sources);
//                        $anime->touch();
                } else {
                    $this->output->info('Missing MAL ID: ' . $sources['mal_id'] ?? 'N/A');
                    $this->output->info('Missing anime: ' . print_r($sources, true));

                    $this->call('scrape:mal_anime', ['malID' => $sources['mal_id']]);

                    $anime = Anime::withoutGlobalScopes()
                        ->where('mal_id', '=', $sources['mal_id'])
                        ->first();

                    // If it's still empty then it's a dead entry
                    if (!empty($anime)) {
                        $anime->update($sources);
                    }
                }
            }

            $progress = $animes->getPosition();
            $progressBar->setProgress($progress);
        }

        $progressBar->finish();

        return Command::SUCCESS;
    }

    /**
     * Returns an array of the sources as key_id => id.
     *
     * @param mixed $sources
     * @return array
     */
    protected function filterSources(mixed $sources): array
    {
        if (empty($sources)) {
            return $sources;
        }

        $idMapping = [
            'anidb_id' => 'anidb_id',
            'anilist_id' => 'anilist_id',
            'animeplanet_id' => 'anime-planet_id',
            'anisearch_id' => 'anisearch_id',
            'kitsu_id' => 'kitsu_id',
            'livechart_id' => 'livechart_id',
            'mal_id' => 'mal_id',
            'notify_id' => 'notify.moe_id',
            'tvdb_id' => 'thetvdb_id',
        ];
        $matchedSources = [];

        // Collect all possible IDs
        foreach ($idMapping as $kurozoraID => $mapID) {
            if (property_exists($sources, $mapID)) {
                $matchedSources[$kurozoraID] = $sources->{$mapID};
            }

        }

        // Check Notify.moe for other IDs
        if (array_key_exists('notify_id', $matchedSources)) {
            $matchedSources = array_merge($matchedSources, $this->getIDsFromNotify($matchedSources));
        }

        return $matchedSources;
    }

    /**
     * Returns an array of the IDs from the notify API.
     *
     * @param array $ids
     * @return array
     */
    protected function getIDsFromNotify(array $ids): array
    {
        $notifyID = $ids['notify_id'];
        $response = Http::timeout(120)
            ->get('https://notify.moe/api/anime/' . $notifyID);
        $cleanSources = [];

        if ($response->failed()) {
            return $cleanSources;
        }

        $sources = $response->json('mappings');

        foreach ($sources as $source) {
            switch ($source['service']) {
                case 'imdb/anime':
                    $cleanSources['imdb_id'] = $source['serviceId'];
                    break;
                case 'thetvdb/anime':
                    if (!array_key_exists('tvdb_id', $ids)) {
                        $cleanSources['tvdb_id'] = $this->getTVDBId($source['serviceId']);
                    }
                    break;
                case 'shoboi/anime':
                    $cleanSources['syoboi_id'] = $source['serviceId'];
                    break;
                case 'trakt/anime':
                    $cleanSources['trakt_id'] = $source['serviceId'];
                    break;
                default: break;
            }
        }

        return $cleanSources;
    }

    /**
     * Cleans and returns the TVDB ID from the given string.
     *
     * @param string $id
     * @return string
     */
    protected function getTVDBId(string $id): string
    {
        $matchCount = preg_match('/^(\d+)\/.*/i', $id, $tvdbId);

        if ($matchCount) {
            return $tvdbId[1];
        }

        return $id;
    }
}
