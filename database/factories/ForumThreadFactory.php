<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ForumSection;
use App\Models\ForumThread;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(ForumThread::class, function (Faker $faker) {
    return [
        'section_id'    => (ForumSection::inRandomOrder()->limit(1)->first())->id,
        'user_id'       => (User::inRandomOrder()->limit(1)->first())->id,
        'ip'            => $faker->ipv4,
        'title'         => $faker->sentence(3),
        'content'       => $faker->paragraph(2),
        'locked'        => $faker->boolean()
     ];
});
