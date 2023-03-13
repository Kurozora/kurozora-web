<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaStudioFactory extends Factory
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
            'model_type' => $anime->getMorphClass(),
            'model_id' => $anime->id,
            'studio_id' => $studio->id,
            'is_licensor' => $this->faker->boolean,
            'is_producer' => $this->faker->boolean,
            'is_studio' => $this->faker->boolean,
            'is_publisher' => $this->faker->boolean,
            'is_developer' => $this->faker->boolean,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
