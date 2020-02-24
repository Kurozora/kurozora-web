<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Session;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Session::class, function (Faker $faker) {
    return [
        'user_id'           => factory(\App\User::class)->create()->id,
        'expiration_date'   => now()->addDays(90),
        'last_validated'    => now(),
        'ip'                => $faker->ipv4,
        'device'            => 'Faker Factory',
        'apn_device_token'  => Str::random(64),
        'secret'            => Str::random(128),
        'city'              => $faker->city,
        'region'            => $faker->state,
        'country'           => $faker->country,
        'latitude'          => $faker->latitude,
        'longitude'         => $faker->longitude
    ];
});
