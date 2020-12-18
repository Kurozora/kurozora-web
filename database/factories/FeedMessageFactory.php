<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FeedMessage;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(FeedMessage::class, function (Faker $faker) {
    return [
        'user_id'                   => factory(User::class)->create()->id,
        'parent_feed_message_id'    => null,
        'body'                      => $faker->sentence
    ];
});
