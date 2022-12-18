<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\Season;
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
        $season = Season::inRandomOrder()->first();

        if ($season == null) {
            $season = Season::factory()->create();
        }

        return [
            'season_id'     => $season,
            'number'        => $number++,
            'number_total'  => $number,
            'title'         => $this->faker->sentence,
            'synopsis'      => $this->faker->realText(),
            'ja'            => [
                'title'     => $jaFaker->sentence,
                'synopsis'  => $jaFaker->realText(),
            ],
            'duration'      => $this->faker->numberBetween(600, 1440),
            'started_at'    => $this->faker->dateTime(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
