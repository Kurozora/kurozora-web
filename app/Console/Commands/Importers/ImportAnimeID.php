<?php

namespace App\Console\Commands\Importers;

use App\Models\Anime;
use App\Scopes\TvRatingScope;
use Http;
use Illuminate\Console\Command;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\JsonMachine;
use function storage_path;

class ImportAnimeID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime_ids';

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
//            if ($animes->getPosition() >= 24594500) {
                $sources = $this->filterSources($data->sources);

                if (array_key_exists('mal_id', $sources) && array_key_exists('notify_id', $sources)) {
                    $anime = Anime::withoutGlobalScope(new TvRatingScope)->firstWhere([
                        ['mal_id', $sources['mal_id']],
                    ]);

                    if (!empty($anime)) {
                        $anime->update($sources);
                    }
                }
//            }

            $progress = $animes->getPosition();
            $progressBar->setProgress($progress);
        }

        $progressBar->finish();

        return Command::SUCCESS;
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

                if (array_key_exists('notify_id', $matchedSources)) {
                    $matchedSources = array_merge($matchedSources, $this->getIDsFromNotify($matchedSources['notify_id']));
                }
            }
        }

        return $matchedSources;
    }

    /**
     * Returns an array of the IDs from the notify API.
     *
     * @param string $notifyID
     * @return array
     */
    protected function getIDsFromNotify(string $notifyID): array
    {
        $response = Http::get('https://notify.moe/api/anime/' . $notifyID);
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
                    $cleanSources['tvdb_id'] = $this->getTVDBId($source['serviceId']);
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
