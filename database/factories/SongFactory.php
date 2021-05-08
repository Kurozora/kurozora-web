<?php

namespace Database\Factories;

use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class SongFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Song::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title'         => $this->faker->unique()->words(mt_rand(1, 4), true),
            'artist'        => $this->faker->unique()->name,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
