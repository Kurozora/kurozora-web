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
    public function definition(): array
    {
        $name = $this->faker->unique()->company;
        return [
            'slug'          => str($name)->slug(),
            'name'          => $name,
            'type'          => 'anime',
            'about'         => $this->faker->realText(),
            'address'       => $this->faker->address(),
            'founded'       => $this->faker->date(),
            'website_urls'  => $this->faker->randomElement([[$this->faker->url], null]),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
