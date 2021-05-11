<?php

namespace Database\Factories;

use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Status::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'type'          => $this->faker->randomElement(['anime', 'manga']),
            'name'          => $this->faker->words(2, true),
            'description'   => $this->faker->sentence(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
