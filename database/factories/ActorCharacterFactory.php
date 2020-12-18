<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Actor;
use App\Models\ActorCharacter;
use App\Models\Character;
use Faker\Generator as Faker;

$factory->define(ActorCharacter::class, function (Faker $faker) {
    return [
        'actor_id'      => factory(Actor::class)->create()->id,
        'character_id'  => factory(Character::class)->create()->id,
    ];
});
