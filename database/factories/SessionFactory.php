<?php

/** @var Factory $factory */

use App\Models\Session;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

$factory->define(Session::class, function (Faker $faker) {
    return [
        'user_id'           => factory(User::class)->create()->id,
        'expires_at'        => now()->addDays(90),
        'last_validated_at' => now(),
        'ip'                => $faker->ipv4,
        'apn_device_token'  => null,
        'secret'            => Str::random(128),
        'city'              => $faker->city,
        'region'            => $faker->state,
        'country'           => $faker->country,
        'latitude'          => $faker->latitude,
        'longitude'         => $faker->longitude
    ];
});
