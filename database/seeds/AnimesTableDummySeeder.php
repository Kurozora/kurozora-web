<?php

use App\Anime;
use Illuminate\Database\Seeder;

class AnimesTableDummySeeder extends Seeder
{
    // Dummy posters
    const DUMMY_CACHED_POSTERS = [
        'https://www.thetvdb.com/banners/posters/81797-32.jpg',
        'https://www.thetvdb.com/banners/posters/74796-15.jpg',
        'https://www.thetvdb.com/banners/posters/75579-8.jpg',
        'https://www.thetvdb.com/banners/posters/79824-36.jpg',
        'https://www.thetvdb.com/banners/posters/244061-4.jpg',
        'https://www.thetvdb.com/banners/posters/79895-28.jpg'
    ];

    const DUMMY_CACHED_POSTERS_THUMBNAILS = [
        'https://www.thetvdb.com/banners/_cache/posters/81797-32.jpg',
        'https://www.thetvdb.com/banners/_cache/posters/74796-15.jpg',
        'https://www.thetvdb.com/banners/_cache/posters/75579-8.jpg',
        'https://www.thetvdb.com/banners/_cache/posters/79824-36.jpg',
        'https://www.thetvdb.com/banners/_cache/posters/244061-4.jpg',
        'https://www.thetvdb.com/banners/_cache/posters/79895-28.jpg'
    ];

    // Dummy backgrounds
    const DUMMY_CACHED_BACKGROUNDS = [
        'https://www.thetvdb.com/banners/fanart/original/79895-15.jpg',
        'https://www.thetvdb.com/banners/fanart/original/81797-23.jpg',
        'https://www.thetvdb.com/banners/fanart/original/81797-3.jpg',
        'https://www.thetvdb.com/banners/fanart/original/81797-19.jpg',
        'https://www.thetvdb.com/banners/fanart/original/79824-24.jpg',
        'https://www.thetvdb.com/banners/fanart/original/79824-44.jpg'
    ];

    const DUMMY_CACHED_BACKGROUNDS_THUMBNAILS = [
        'https://www.thetvdb.com/banners/_cache/fanart/original/79895-15.jpg',
        'https://www.thetvdb.com/banners/_cache/fanart/original/81797-23.jpg',
        'https://www.thetvdb.com/banners/_cache/fanart/original/81797-3.jpg',
        'https://www.thetvdb.com/banners/_cache/fanart/original/81797-19.jpg',
        'https://www.thetvdb.com/banners/_cache/fanart/original/79824-24.jpg',
        'https://www.thetvdb.com/banners/_cache/fanart/original/79824-44.jpg'
    ];

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
            // Pick a random poster and background
            $randomPoster       = array_rand(self::DUMMY_CACHED_POSTERS);
            $randomBackground   = array_rand(self::DUMMY_CACHED_BACKGROUNDS);

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
                'cached_poster'                 => self::DUMMY_CACHED_POSTERS[$randomPoster],
                'cached_poster_thumbnail'       => self::DUMMY_CACHED_POSTERS_THUMBNAILS[$randomPoster],
                'cached_background'             => self::DUMMY_CACHED_BACKGROUNDS[$randomBackground],
                'cached_background_thumbnail'   => self::DUMMY_CACHED_BACKGROUNDS_THUMBNAILS[$randomBackground],
                'type'                          => self::DUMMY_ANIME_TYPES[$randomType],
                'nsfw'                          => $nsfw,
                'tvdb_id'                       => self::DUMMY_TVDB_IDS[$randomTVDBID]
            ]);
        }
    }
}
