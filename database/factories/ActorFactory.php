<?php

namespace Database\Factories;

use App\Models\Actor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Actor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $isFemale = mt_rand(0, 1);
        $genderString = $isFemale ? 'female' : 'male';

        return [
            'first_name'    => $this->faker->firstName($genderString),
            'last_name'     => $this->faker->lastName($genderString),
            'about'         => $this->faker->paragraph(mt_rand(10, 30)),
            'occupation'    => $this->faker->jobTitle,
            'image'         => $this->faker->imageUrl()
        ];
    }
}
