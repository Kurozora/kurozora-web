<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Actor;
use Faker\Generator as Faker;

$factory->define(Actor::class, function (Faker $faker) {
    $gender = mt_rand(0, 1);
    $genderString = $gender ? 'female' : 'male';

    return [
        'first_name'    => $faker->firstName($genderString),
        'last_name'     => $faker->lastName($genderString),
        'occupation'    => $faker->jobTitle,
        'image'         => $faker->imageUrl()
    ];
});
