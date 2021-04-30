<?php

namespace Database\Factories;

use App\Models\MediaSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaSourceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MediaSource::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'          => $this->faker->word,
            'description'   => $this->faker->words(3, true),
        ];
    }
}
