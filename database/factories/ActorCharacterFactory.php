<?php

namespace Database\Factories;

use App\Models\Actor;
use App\Models\ActorCharacter;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActorCharacterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ActorCharacter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $actor = Actor::inRandomOrder()->first();
        $character = Character::inRandomOrder()->first();

        if ($actor == null) {
            $actor = Actor::factory()->create();
        }
        if ($character == null) {
            $character = Character::factory()->create();
        }

        return [
            'actor_id'      => $actor,
            'character_id'  => $character,
        ];
    }
}
