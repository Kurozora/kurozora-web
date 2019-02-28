<?php

use App\AppTheme;
use Faker\Generator as Faker;

$factory->define(AppTheme::class, function (Faker $faker) {
    return [
        'name'                  => $faker->name,
        'background_color'      => $faker->hexcolor,
        'text_color'            => $faker->hexcolor,
        'tint_color'            => $faker->hexcolor,
        'bar_tint_color'        => $faker->hexcolor,
        'bar_title_text_color'  => $faker->hexcolor,
    ];
});
