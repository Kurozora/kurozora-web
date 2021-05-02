<?php

namespace Database\Factories;

use App\Models\AnimeEpisode;
use App\Models\AnimeSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeEpisodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AnimeEpisode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        static $number = 1;
        $animeSeason = AnimeSeason::inRandomOrder()->first();

        if ($animeSeason == null) {
            $animeSeason = AnimeSeason::factory()->create();
        }

        return [
            'season_id'     => $animeSeason,
            'title'         => $this->faker->title,
            'number'        => $number++,
            'overview'      => $this->faker->realText(),
            'first_aired'   => $this->faker->dateTime()
        ];
    }
}
