<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\FeedMessage;
use App\User;
use Faker\Generator as Faker;

$factory->define(FeedMessage::class, function (Faker $faker) {
    return [
        'user_id'   => factory(User::class)->create()->id,
        'body'      => $faker->sentence
    ];
});
