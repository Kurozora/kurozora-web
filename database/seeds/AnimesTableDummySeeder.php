<?php

use App\Anime;
use Illuminate\Database\Seeder;

class AnimesTableDummySeeder extends Seeder
{
    // Dummy TVDB ID's
    const DUMMY_TVDB_IDS = [
        79824,
        81797,
        75579,
        85249,
        74796,
        339268,
        244061
    ];

    // Dummy anime types
    const DUMMY_ANIME_TYPES = [
        Anime::ANIME_TYPE_TV,
        Anime::ANIME_TYPE_MOVIE
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        // Create 50 random Animes
        foreach(range(1, 50) as $index) {
            // Pick a random type
            $randomType         = array_rand(self::DUMMY_ANIME_TYPES);

            // Pick NSFW or not
            $nsfw = false;

            if(rand(0, 10) == 0)
                $nsfw = true;

            // Pick a TVDB ID
            $randomTVDBID = array_rand(self::DUMMY_TVDB_IDS);

            Anime::create([
                'title'                         => $faker->sentence(3),
                'type'                          => self::DUMMY_ANIME_TYPES[$randomType],
                'nsfw'                          => $nsfw,
                'tvdb_id'                       => self::DUMMY_TVDB_IDS[$randomTVDBID]
            ]);
        }
    }
}
