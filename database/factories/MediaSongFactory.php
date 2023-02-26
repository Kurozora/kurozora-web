<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\MediaSong;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaSongFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MediaSong::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $model = Anime::inRandomOrder(mt_rand(1, 999))->first();
        if ($model == null) {
            $model = Anime::factory()->create();
        }

        $song = Song::inRandomOrder(mt_rand(1, 999))->first();
        if ($song == null) {
            $song = Song::factory()->create();
        }

        return [
            'model_id'      => $model,
            'song_id'       => $song,
            'type'          => $this->faker->randomElement(['Opening', 'Ending', 'Background']),
            'position'      => $this->faker->unique()->numberBetween(1, 24),
            'Episodes'      => $this->faker->randomElement(['1-12', '1-12, 14-24', '1, 5, 7, 13', '33']),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
