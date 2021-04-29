<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\MediaType;
use App\Models\TvRating;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AnimeDummySeeder extends Seeder
{
    // URL to retrieve Anime from
    const ANIME_JSON_FILE = 'https://raw.githubusercontent.com/Kurozora/anime/master/anime.json';

    // The path where the retrieved json file is saved
    const ANIME_JSON_PATH = 'storage/app/anime.json';

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function run()
    {
        // Get the anime JSON
        if (!Storage::exists(self::ANIME_JSON_PATH)) {
            $this->command->info('Downloading anime JSON...');
            $animeJSON = self::downloadJSON();
            $this->command->info('Anime JSON downloaded.');
        } else {
            $this->command->info('Using downloaded anime JSON.');
            $animeJSON = Storage::get(self::ANIME_JSON_PATH);
        }

        // Parse the JSON
        $parsedAnime = json_decode($animeJSON);

        if ($parsedAnime != null) {
            $total = count($parsedAnime->anime);
            $this->command->info('Start importing ' . $total . ' anime...');

            // Loop through all anime
            foreach ($parsedAnime->anime as $index => $animeData) {
                $count = $index + 1;
                // Respect the maximum amount
                if (Env('MAX_ANIME_TO_SEED') != null) {
                    // Break out of the loop if the max has been reached
                    if ($count >= Env('MAX_ANIME_TO_SEED'))
                        break;
                }

                // Create the anime
                Anime::create([
                    'anidb_id'      => $animeData->anidb_id,
                    'anilist_id'    => $animeData->anilist_id,
                    'kitsu_id'      => $animeData->kitsu_id,
                    'mal_id'        => $animeData->mal_id,
                    'tvdb_id'       => $animeData->tvdb_id,
                    'slug'          => $animeData->slug,
                    'title'         => $animeData->title,
                    'media_type_id' => MediaType::where('type', 'anime')->inRandomOrder()->first()->id,
                    'tv_rating_id'  => TvRating::inRandomOrder()->first()->id,
                    'is_nsfw'       => $animeData->nsfw,
                ]);

                // Print progress every 100 anime and at the last anime
                if ($count % 100 == 0 || $count == $total)
                    $this->command->info($count . '/' . $total . ' anime imported.');
            }
        } else {
            $this->command->info('No anime parsed.');
        }

        $this->command->info('Finished importing anime.');
    }

    /**
     * Store and return the stored json.
     *
     * @return false|string
     */
    static function downloadJSON(): bool|string
    {
        $pathToAnimeJSON = AnimeDummySeeder::ANIME_JSON_PATH;

        // Delete file if it exists
        if (Storage::exists($pathToAnimeJSON)) {
            Storage::delete($pathToAnimeJSON);
        }

        // Download the file
        $animeJSON = file_get_contents(self::ANIME_JSON_FILE);

        // Store data locally
        Storage::put(self::ANIME_JSON_PATH, $animeJSON);

        return $animeJSON;
    }
}
