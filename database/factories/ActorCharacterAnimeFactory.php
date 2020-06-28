<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ActorCharacter;
use App\ActorCharacterAnime;
use App\Anime;
use App\Enums\CharacterRole;
use App\Enums\PersonRole;
use Faker\Generator as Faker;

$factory->define(ActorCharacterAnime::class, function (Faker $faker) {
    return [
        'actor_character_id'    => factory(ActorCharacter::class)->create()->id,
        'anime_id'              => factory(Anime::class)->create()->id,
        'actor_role'            => array_rand(PersonRole::toArray()),
        'character_role'        => array_rand(CharacterRole::toArray()),
    ];
});
