<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Anime;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use Faker\Generator as Faker;

$factory->define(Anime::class, function (Faker $faker) {
    return [
        'title'     => $faker->sentence,
        'status'    => AnimeStatus::getRandomValue(),
        'type'      => AnimeType::getRandomValue()
    ];
});
