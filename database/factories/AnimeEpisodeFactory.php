<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\AnimeEpisode;
use App\AnimeSeason;
use Faker\Generator as Faker;

$factory->define(AnimeEpisode::class, function (Faker $faker) {
    static $number = 1;

    return [
        'season_id'     => factory(AnimeSeason::class)->create()->id,
        'title'         => $faker->title,
        'number'        => $number++,
        'overview'      => $faker->realText(),
        'first_aired'   => $faker->dateTime()
    ];
});
