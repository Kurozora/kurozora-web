<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    // URL to retrieve genres from
    const GENRE_JSON_FILE = 'https://raw.githubusercontent.com/Kurozora/anime/master/anime-genres.json';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Retrieve data from URL
        $genreJson = file_get_contents(self::GENRE_JSON_FILE);
        $parsedGenres = json_decode($genreJson);

        if ($parsedGenres != null) {
            // Create genres
            foreach ($parsedGenres->genres as $genre) {
                Genre::create([
                    'tv_rating_id'  => $genre->nsfw ? 5 : 2,
                    'name'          => $genre->name,
                    'description'   => $genre->description,
                    'is_nsfw'       => $genre->nsfw,
                    'color'         => $genre->color
                ]);
            }
        }
    }
}
