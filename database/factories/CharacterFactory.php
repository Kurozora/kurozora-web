<?php

namespace Database\Factories;

use App\Enums\CharacterStatus;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Character::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $jaFaker = \Faker\Factory::create('ja_JP');
        $month = $this->faker->month;
        $isFemale = mt_rand(0, 1);
        $genderString = $isFemale ? 'female' : 'male';
        $name = $this->faker->name($genderString);
        $bust = null;
        $waist = null;
        $hip = null;

        if ($isFemale) {
            $bust = $this->faker->randomFloat(2, 20, 80);
            $waist = $this->faker->randomFloat(2, 20, 80);
            $hip = $this->faker->randomFloat(2, 20, 80);
        }

        return [
            'slug'              => str($name)->slug(),
            'name'              => $name,
            'nicknames'         => $this->faker->words(mt_rand(0, 3)),
            'about'             => $this->faker->realText(),
            'ja'                => [
                'name'  => $jaFaker->name($genderString),
                'about' => $jaFaker->paragraph
            ],
            'debut'             => $this->faker->numerify('Episode ##'),
            'status'            => CharacterStatus::getRandomValue(),
            'blood_type'        => strtoupper($this->faker->randomLetter),
            'favorite_food'     => $this->faker->word,
            'height'            => $this->faker->numberBetween(10, 1000),
            'bust'              => $bust,
            'waist'             => $waist,
            'hip'               => $hip,
            'age'               => $this->faker->numberBetween(1, 300),
            'birth_day'         => (int) $this->faker->dayOfMonth($month),
            'birth_month'       => (int) $month,
            'astrological_sign' => $this->faker->numberBetween(0, 11),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
