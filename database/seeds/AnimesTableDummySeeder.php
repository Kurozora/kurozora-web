<?php

use App\Anime;
use App\Enums\AnimeType;
use Illuminate\Database\Seeder;
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
        $animeJSON = null;
        if(!Storage::exists(self::ANIME_JSON_PATH)) {
            $animeJSON = self::storeJSON();
        } else {
            $animeJSON = Storage::get(self::ANIME_JSON_PATH);
        }

        $parsedAnime = json_decode($animeJSON);

        if($parsedAnime != null) {
            // Create the Anime
            foreach ($parsedAnime->anime as $animeData) {
                Anime::create([
                    'title'     => $animeData->title,
                    'type'      => AnimeType::TV,
                    'nsfw'      => $animeData->nsfw,
                    'tvdb_id'   => $animeData->tvdb_id,
                    'mal_id'    => $animeData->mal_id
                ]);
            }
        }
    }

    /**
     * Store and return the stored json.
     *
     * @return false|string
     */
    static function storeJSON() {
        // Retrieve data from URL
        $animeJSON = file_get_contents(self::ANIME_JSON_FILE);

        // Store data locally
        Storage::put(self::ANIME_JSON_PATH, $animeJSON);

        return $animeJSON;
    }
}
