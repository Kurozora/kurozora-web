<?php

namespace Database\Factories;

use App\Models\Actor;
use App\Models\AnimeCast;
use App\Models\Anime;
use App\Models\CastRole;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeCastFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AnimeCast::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $actor = Actor::inRandomOrder(mt_rand(1, 999))->first();
        $character = Character::inRandomOrder(mt_rand(1, 999))->first();
        $anime = Anime::inRandomOrder(mt_rand(1, 999))->first();
        $castRole = CastRole::inRandomOrder()->first();

        if ($actor == null) {
            $actor = Actor::factory()->create();
        }
        if ($character == null) {
            $character = Character::factory()->create();
        }
        if ($anime == null) {
            $anime = Anime::factory()->create();
        }
        if ($castRole == null) {
            $castRole = CastRole::factory()->create();
        }

        return [
            'actor_id'      => $actor,
            'character_id'  => $character,
            'anime_id'      => $anime,
            'cast_role_id'  => $castRole,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
