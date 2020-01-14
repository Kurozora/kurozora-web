<?php

use App\Anime;
use App\Enums\AnimeType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class AnimesTableDummySeeder extends Seeder
{
    // URL to retrieve Anime from
    const ANIME_JSON_FILE = 'https://raw.githubusercontent.com/Kurozora/anime/master/anime.json';

    // The path where the retrieved json file is saved
    const ANIME_JSON_PATH = 'storage/app/anime.json';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the anime JSON
        $animeJSON = null;

        if(!Storage::exists(self::ANIME_JSON_PATH)) {
            $this->command->info('Downloading anime JSON...');
            $animeJSON = self::downloadJSON();
            $this->command->info('Anime JSON downloaded.');
        } else {
            $this->command->info('Using downloaded anime JSON.');
            $animeJSON = Storage::get(self::ANIME_JSON_PATH);
        }

        // Parse the JSON
        $parsedAnime = json_decode($animeJSON);

        if($parsedAnime != null) {
            $total = count($parsedAnime->anime);
            $this->command->info('Start importing ' . $total . ' anime...');

            // Loop through all anime
            foreach ($parsedAnime->anime as $index => $animeData) {
                $count = $index + 1;
                // Respect the maximum if the app is being test
                if (App::environment('testing')) {
                    // Break out of the loop if the max has been reached
                    if($count >= env('MAX_ANIME_TO_SEED', 10))
                        break;
                }

                // Create the anime
                Anime::create([
                    'title'         => $animeData->title,
                    'type'          => AnimeType::TV,
                    'nsfw'          => $animeData->nsfw,
                    'anidb_id'      => $animeData->anidb_id,
                    'anilist_id'    => $animeData->anilist_id,
                    'kitsu_id'      => $animeData->kitsu_id,
                    'mal_id'        => $animeData->mal_id,
                    'tvdb_id'       => $animeData->tvdb_id,
                    'slug'          => $animeData->slug
                ]);

                // Print progress every 100 anime and at the last anime
                if($count % 100 == 0 || $count == $total)
                    $this->command->info($count . '/' . $total . ' anime imported.');
            }
        }
        else {
            $this->command->info('No anime parsed.');
        }

        $this->command->info('Finished importing anime.');
    }

    /**
     * Store and return the stored json.
     *
     * @return false|string
     */
    static function downloadJSON() {
        $pathToAnimeJSON = AnimesTableDummySeeder::ANIME_JSON_PATH;

        // Delete file if it exists
        if (Storage::exists($pathToAnimeJSON))
            Storage::delete($pathToAnimeJSON);

        // Download the file
        $animeJSON = file_get_contents(self::ANIME_JSON_FILE);

        // Store data locally
        Storage::put(self::ANIME_JSON_PATH, $animeJSON);

        return $animeJSON;
    }
}
