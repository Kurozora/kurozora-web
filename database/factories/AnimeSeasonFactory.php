<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Anime;
use App\AnimeSeason;
use Faker\Generator as Faker;

$factory->define(AnimeSeason::class, function (Faker $faker) {
	static $number = 1;

    return [
        'anime_id'     => factory(Anime::class)->create()->id,
        'number'       => $number++,
        'title'        => $faker->title
    ];
});
