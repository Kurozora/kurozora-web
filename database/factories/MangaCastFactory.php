<?php

namespace Database\Factories;

use App\Models\CastRole;
use App\Models\Character;
use App\Models\Manga;
use App\Models\MangaCast;
use Illuminate\Database\Eloquent\Factories\Factory;

class MangaCastFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MangaCast::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $character = Character::inRandomOrder(mt_rand(1, 999))->first();
        $manga = Manga::inRandomOrder(mt_rand(1, 999))->first();
        $castRole = CastRole::inRandomOrder()->first();

        if ($character == null) {
            $character = Character::factory()->create();
        }
        if ($manga == null) {
            $manga = Manga::factory()->create();
        }
        if ($castRole == null) {
            $castRole = CastRole::factory()->create();
        }

        return [
            'character_id'  => $character,
            'manga_id'      => $manga,
            'cast_role_id'  => $castRole,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
