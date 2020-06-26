<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Actor;
use App\ActorAnimeCharacter;
use App\Anime;
use App\Character;
use Faker\Generator as Faker;

$factory->define(ActorAnimeCharacter::class, function (Faker $faker) {
    return [
        'actor_id'      => factory(Actor::class)->create()->id,
        'anime_id'      => factory(Anime::class)->create()->id,
        'character_id'  => factory(Character::class)->create()->id,
    ];
});
