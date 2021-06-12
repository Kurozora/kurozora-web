<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\AnimeSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

class EpisodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Episode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        static $number = 1;
        $jaFaker = \Faker\Factory::create('ja_JP');
        $animeSeason = AnimeSeason::inRandomOrder()->first();

        if ($animeSeason == null) {
            $animeSeason = AnimeSeason::factory()->create();
        }

        return [
            'season_id'     => $animeSeason,
            'number'        => $number++,
            'title'         => $this->faker->sentence,
            'overview'      => $this->faker->realText(),
            'ja'            => [
                'title'     => $jaFaker->sentence,
                'overview'  => $jaFaker->realText(),
            ],
            'first_aired'   => $this->faker->dateTime(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
