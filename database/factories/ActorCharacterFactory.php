<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Actor;
use App\ActorCharacter;
use App\Character;
use Faker\Generator as Faker;

$factory->define(ActorCharacter::class, function (Faker $faker) {
    return [
        'actor_id'      => factory(Actor::class)->create()->id,
        'character_id'  => factory(Character::class)->create()->id,
    ];
});
