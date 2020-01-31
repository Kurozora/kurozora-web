<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Anime;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Anime::class, function (Faker $faker) {
    $title = $faker->sentence;

    return [
        'title'         => $title,
        'tagline'       => $faker->sentence,
        'video_url'     => null,
        'network'       => null,
        'synopsis'      => $faker->paragraph,
        'runtime'       => 25,
        'watch_rating'  => $faker->randomElement(['G', 'PG', 'PG-13', 'R', 'NC-17']),
        'status'        => AnimeStatus::getRandomValue(),
        'type'          => AnimeType::getRandomValue(),
        'nsfw'          => $faker->boolean,
        'anidb_id'      => null,
        'anilist_id'    => null,
        'kitsu_id'      => null,
        'mal_id'        => null,
        'tvdb_id'       => null,
        'slug'          => Str::slug($title),
        'first_aired'   => $faker->dateTime,
        'air_time'      => null,
        'air_day'       => null
    ];
});
