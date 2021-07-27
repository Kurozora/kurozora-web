<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeasonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Season::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        static $number = 1;
        $jaFaker = \Faker\Factory::create('ja_JP');
        $anime = Anime::inRandomOrder()->first();

        if ($anime == null) {
            $anime = Anime::factory()->create();
        }

        return [
            'anime_id'      => $anime,
            'number'        => $number++,
            'title'         => $this->faker->sentence,
            'synopsis'      => $this->faker->realText(),
            'ja'            => [
                'title'     => $jaFaker->sentence,
                'synopsis'  => $jaFaker->realText(),
            ],
            'first_aired'   => $this->faker->dateTime,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
