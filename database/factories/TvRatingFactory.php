<?php

namespace Database\Factories;

use App\Models\TvRating;
use Illuminate\Database\Eloquent\Factories\Factory;

class TvRatingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TvRating::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->randomLetter,
            'description'   => $this->faker->words(3, true),
            'weight'        => $this->faker->unique()->numberBetween(1, 10),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
