<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ForumReply;
use App\ForumThread;
use App\User;
use Faker\Generator as Faker;

$factory->define(ForumReply::class, function (Faker $faker) {
    return [
        'thread_id'     => (ForumThread::inRandomOrder()->limit(1)->first())->id,
        'user_id'       => (User::inRandomOrder()->limit(1)->first())->id,
        'ip'            => $faker->ipv4,
        'content'       => $faker->paragraph(2)
     ];
});
