<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->name,
            'code'          => $this->faker->languageCode,
            'iso_639_3'     => $this->faker->countryISOAlpha3,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
