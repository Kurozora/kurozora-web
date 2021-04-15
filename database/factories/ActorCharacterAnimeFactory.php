<?php

namespace Database\Factories;

use App\Models\ActorCharacter;
use App\Models\ActorCharacterAnime;
use App\Models\Anime;
use App\Enums\CastRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActorCharacterAnimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ActorCharacterAnime::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $actorCharacter = ActorCharacter::inRandomOrder()->first();
        $anime = Anime::inRandomOrder()->first();

        if ($actorCharacter == null) {
            $actorCharacter = ActorCharacter::factory()->create();
        }
        if ($anime == null) {
            $anime = Anime::factory()->create();
        }
        if (ActorCharacterAnime::where('anime_id', $anime->id)->exists()) {
            $anime = Anime::whereNotIn('id', $anime->id)->inRandomOrder()->first();
        }

        return [
            'actor_character_id'    => $actorCharacter,
            'anime_id'              => $anime,
            'cast_role'             => array_rand(CastRole::getValues()),
        ];
    }
}
