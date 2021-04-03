<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Anime;
use App\Models\AnimeRelations;
use App\Enums\AnimeRelationType;
use Faker\Generator as Faker;

$factory->define(AnimeRelations::class, function (Faker $faker) {
    return [
        'anime_id'          => factory(Anime::class)->create()->id,
        'related_anime_id'  => factory(Anime::class)->create()->id,
        'type'              => AnimeRelationType::getRandomValue()
    ];
});
