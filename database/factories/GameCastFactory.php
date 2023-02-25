<?php

namespace Database\Factories;

use App\Models\CastRole;
use App\Models\Character;
use App\Models\Game;
use App\Models\GameCast;
use App\Models\Language;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameCastFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GameCast::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $person = Person::inRandomOrder(mt_rand(1, 999))->first();
        $character = Character::inRandomOrder(mt_rand(1, 999))->first();
        $game = Game::inRandomOrder(mt_rand(1, 999))->first();
        $castRole = CastRole::inRandomOrder()->first();
        $language = Language::inRandomOrder()->first();

        if ($person == null) {
            $person = Person::factory()->create();
        }
        if ($character == null) {
            $character = Character::factory()->create();
        }
        if ($game == null) {
            $game = Game::factory()->create();
        }
        if ($castRole == null) {
            $castRole = CastRole::factory()->create();
        }
        if ($language == null) {
            $language = Language::factory([
                'id' => 73 // japanese
            ])->create();
        }

        return [
            'person_id'     => $person,
            'character_id'  => $character,
            'game_id'       => $game,
            'cast_role_id'  => $castRole,
            'language_id'   => $language,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
