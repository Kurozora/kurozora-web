<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Person::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $isFemale = mt_rand(0, 1);
        $genderString = $isFemale ? 'female' : 'male';
        $name = $this->faker->firstName($genderString);
        $jpFaker = \Faker\Factory::create('ja_JP');

        return [
            'slug'              => str($name)->slug(),
            'first_name'        => $name,
            'last_name'         => $this->faker->randomElement([$this->faker->lastName, null]),
            'given_name'        => $this->faker->randomElement([$jpFaker->firstName($genderString), null]),
            'family_name'       => $this->faker->randomElement([$jpFaker->lastName, null]),
            'alternative_names' => $this->faker->randomElement([$this->faker->words(mt_rand(0, 3)), null]),
            'birthdate'         => $this->faker->randomElement([$this->faker->date(), null]),
            'about'             => $this->faker->randomElement([$this->faker->realText(), null]),
            'website_urls'      => $this->faker->randomElement([[$this->faker->url], null]),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
