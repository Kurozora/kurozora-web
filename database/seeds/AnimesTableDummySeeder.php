<?php

use App\Anime;
use Illuminate\Database\Seeder;

class AnimesTableDummySeeder extends Seeder
{
    // URL to retrieve Anime from
    const ANIME_JSON_FILE = 'https://raw.githubusercontent.com/Kurozora/anime/master/anime.json';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Retrieve data from URL
        $animeJson = file_get_contents(self::ANIME_JSON_FILE);
        $parsedAnime = json_decode($animeJson);

        if($parsedAnime != null) {
            // Create the Anime
            foreach ($parsedAnime->anime as $animeData) {
                Anime::create([
                    'title'     => $animeData->title,
                    'type'      => Anime::ANIME_TYPE_TV,
                    'nsfw'      => $animeData->nsfw,
                    'tvdb_id'   => $animeData->tvdb_id
                ]);
            }
        }
    }
}
