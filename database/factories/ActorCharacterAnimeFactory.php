<?php

namespace Database\Factories;

use App\Models\Actor;
use App\Models\ActorCharacterAnime;
use App\Models\Anime;
use App\Enums\CastRole;
use App\Models\Character;
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
    public function definition(): array
    {
        $actor = Actor::inRandomOrder()->first();
        $character = Character::inRandomOrder()->first();
        $anime = Anime::inRandomOrder()->first();

        if ($actor == null) {
            $actor = Actor::factory()->create();
        }
        if ($character == null) {
            $character = Character::factory()->create();
        }
        if ($anime == null) {
            $anime = Anime::factory()->create();
        }

        return [
            'actor_id'      => $actor,
            'character_id'  => $character,
            'anime_id'      => $anime,
            'cast_role'     => array_rand(CastRole::getValues()),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
