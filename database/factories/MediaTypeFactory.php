<?php

namespace Database\Factories;

use App\Models\MediaType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MediaType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'type'          => $this->faker->randomElement(['anime', 'manga']),
            'name'          => $this->faker->name,
            'description'   => $this->faker->sentence(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
