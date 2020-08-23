<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Character;
use Faker\Generator as Faker;

$factory->define(Character::class, function (Faker $faker) {
    $month = $faker->month;
    $gender = mt_rand(0, 1);
    $genderString = $gender ? 'female' : 'male';
    $bust = null;
    $waist = null;
    $hip = null;

    if ($gender) {
        $bust = $faker->randomFloat(2, 20, 80);
        $waist = $faker->randomFloat(2, 20, 80);
        $hip = $faker->randomFloat(2, 20, 80);
    }

    return [
        'name'              => $faker->name($genderString),
        'about'             => $faker->paragraph(mt_rand(10, 30)),
        'image'             => $faker->imageUrl(),
        'debut'             => $faker->numerify('Episode ##'),
        'status'            => $faker->randomElement(['Alive', 'Deceased']),
        'blood_type'        => strtoupper($faker->randomLetter),
        'favorite_food'     => $faker->word,
        'height'            => $faker->numberBetween(10, 1000),
        'bust'              => $bust,
        'waist'             => $waist,
        'hip'               => $hip,
        'age'               => $faker->numberBetween(1, 300),
        'birth_day'         => (int) $faker->dayOfMonth($month),
        'birth_month'       => (int) $month,
        'astrological_sign' => $faker->numberBetween(0, 11),
    ];
});
