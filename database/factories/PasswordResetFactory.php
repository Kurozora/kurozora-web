<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\PasswordReset;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(PasswordReset::class, function (Faker $faker) {
    return [
        'user_id'       => factory(User::class)->create()->id,
        'ip'            => $faker->ipv4,
        'token'         => PasswordReset::genToken(),
        'created_at'    => now()
    ];
});
