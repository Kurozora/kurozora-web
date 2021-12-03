<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\Language;
use App\Models\Person;
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
        $person = Person::inRandomOrder(mt_rand(1, 999))->first();
        $character = Character::inRandomOrder(mt_rand(1, 999))->first();
        $anime = Anime::inRandomOrder(mt_rand(1, 999))->first();
        $castRole = CastRole::inRandomOrder()->first();
        $language = Language::inRandomOrder()->first();

        if ($person == null) {
            $person = Person::factory()->create();
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
        if ($language == null) {
            $language = Language::factory([
                'id' => 73 // japanese
            ])->create();
        }

        return [
            'person_id'     => $person,
            'character_id'  => $character,
            'anime_id'      => $anime,
            'cast_role_id'  => $castRole,
            'language_id'   => $language,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
