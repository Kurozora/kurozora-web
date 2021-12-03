<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeStudioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $studio = Studio::factory()->create();
        $anime = Anime::factory()->create();

        return [
            'studio_id' => $studio->id,
            'anime_id' => $anime->id,
            'is_licensor' => $this->faker->boolean,
            'is_producer' => $this->faker->boolean,
            'is_studio' => $this->faker->boolean,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
