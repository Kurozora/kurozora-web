<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\AnimeSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeSeasonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AnimeSeason::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        static $number = 1;
        $anime = Anime::inRandomOrder()->first();

        if ($anime == null) {
            $anime = Anime::factory()->create();
        }

        return [
            'anime_id'  => $anime,
            'number'    => $number++,
            'title'     => $this->faker->title
        ];
    }
}
