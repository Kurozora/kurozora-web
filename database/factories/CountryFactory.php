<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Country::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->name,
            'code'          => $this->faker->countryCode,
            'iso_3166_3'    => $this->faker->countryISOAlpha3,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
