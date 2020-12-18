<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Studio;
use Faker\Generator as Faker;

$factory->define(Studio::class, function (Faker $faker) {
    return [
        'name'          => $faker->company,
        'logo_url'      => $faker->imageUrl(),
        'about'         => $faker->paragraph(mt_rand(10, 30)),
        'founded'       => $faker->date(),
        'website_url'   => $faker->url
    ];
});
