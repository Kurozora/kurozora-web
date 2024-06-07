<?php

namespace App\Console\Commands\Importers;

use App\Models\Anime;
use App\Models\Genre;
use App\Models\MediaType;
use App\Models\Relation;
use App\Models\Source;
use App\Models\Tag;
use App\Models\Theme;
use DB;
use Http;
use Illuminate\Console\Command;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

class ImportAnimeOfflineID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime_offline_ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime ids from anime offline database.';

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
        DB::disableQueryLog();
        $file = storage_path('app/anime-offline-database-minified.json');
        $fileSize = filesize($file);
        $animes = Items::fromFile($file, [
            'pointer' => '/data',
            'decoder' => new ExtJsonDecoder,
            'debug' => true
        ]);
//        $cutoff = 100;

        $progressBar = $this->output->createProgressBar($fileSize);
        $progressBar->start();

        foreach ($animes as $data) {
//            if ($animes->getPosition() < 34782445) {
//                continue;
//            }

            $sources = $this->filterSources($data->sources);

//            if (array_key_exists('mal_id', $sources)) {
//                if (!Anime::withoutGlobalScopes()
//                    ->where('mal_id', '=', $sources['mal_id'])->exists()) {
//                    $this->output->write($sources['mal_id'] . ',');
//                    $cutoff-=1;
//                }
//            }

//            if ($cutoff == 0) {
//                $cutoff = 100;
//                $this->newLine();
//                $this->info($animes->getPosition());
//                $this->newLine();
//            } else {
//                $this->output->writeln('Sources: ' . collect($sources)->join(','));
//            }

            if (array_key_exists('mal_id', $sources)) {
                $anime = Anime::withoutGlobalScopes()
                    ->where('mal_id', '=', $sources['mal_id'])
//                        ->whereDate('updated_at', '<', today())
                    ->first();

                if (!empty($anime)
//                        && $anime->tags()->count() == 0
                ) {
                    if (!empty($anime->kitsu_id)) {
                        unset($sources['kitsu_id']);
                    }
                    $anime->update($sources);
//                        $anime->touch();

                    // Add tags
                    $this->addTags($data->tags, $anime);
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

                        // Add tags
                        $this->addTags($data->tags, $anime);
                    }
                }
            }

            $progress = $animes->getPosition();
//                echo intval($animes->getPosition() / $fileSize * 100) . ' %' . PHP_EOL;
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
            'anidb_id' => '#https:\/\/anidb\.net\/anime\/(\w+)$#i',
            'anilist_id' => '#https:\/\/anilist\.co\/anime\/(\w+)$#i',
            'animeplanet_id' => '#https:\/\/anime-planet\.com\/anime\/(.+)$#i',
            'anisearch_id' => '#https:\/\/anisearch\.com\/anime\/(\w+)$#i',
            'kitsu_id' => '#https:\/\/kitsu\.io\/anime\/(\w+)$#i',
            'livechart_id' => '#https:\/\/livechart\.me\/anime\/(\w+)$#i',
            'mal_id' => '#https:\/\/myanimelist\.net\/anime/(\w+)$#i',
            'notify_id' => '#https:\/\/notify\.moe\/anime/(\w+)$#i',
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
        $response = Http::timeout(5)
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

    /**
     * Add tags to anime.
     *
     * @param array $tags
     * @param Anime $anime
     * @return void
     */
    protected function addTags(array $tags, Anime $anime): void
    {
        foreach($tags as $tagName) {
            $tagName = str($tagName)
                ->remove('Based on an', false)
                ->remove('Based on a', false)
                ->remove('Based on', false)
                ->trim()
                ->singular()
                ->ucfirst();

            if (Genre::withoutGlobalScopes()->where('name', '=', $tagName)->exists()) {
                continue;
            } else if (Theme::withoutGlobalScopes()->where('name', '=', $tagName)->exists()) {
                continue;
            } else if (MediaType::withoutGlobalScopes()->where('name', '=', $tagName)->exists()) {
                continue;
            }  else if (Relation::withoutGlobalScopes()->where('name', '=', $tagName)->exists()) {
                continue;
            } else if (Source::withoutGlobalScopes()->where('name', '=', $tagName)->exists()) {
                continue;
            }

            $tag = Tag::withoutGlobalScopes()
                ->firstOrCreate([
                    'name' => $tagName
                ], [
                    'description' => null
                ]);

            $anime->mediaTags()->updateOrCreate([
                'tag_id' => $tag->id
            ]);
        }
    }
}
