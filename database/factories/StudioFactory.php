<?php

namespace Database\Factories;

use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Studio::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'          => $this->faker->unique()->company,
            'logo_url'      => $this->faker->imageUrl(),
            'about'         => $this->faker->paragraph(mt_rand(10, 30)),
            'founded'       => $this->faker->date(),
            'website_url'   => $this->faker->url
        ];
    }
}
