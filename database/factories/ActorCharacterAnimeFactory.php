<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ActorCharacter;
use App\Models\ActorCharacterAnime;
use App\Models\Anime;
use App\Enums\CastRole;
use Faker\Generator as Faker;

$factory->define(ActorCharacterAnime::class, function (Faker $faker) {
    return [
        'actor_character_id'    => factory(ActorCharacter::class)->create()->id,
        'anime_id'              => factory(Anime::class)->create()->id,
        'cast_role'             => array_rand(CastRole::getValues()),
    ];
});
