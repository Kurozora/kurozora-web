<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Enums\DayOfWeek;
use App\Models\MediaSource;
use App\Models\MediaType;
use App\Models\Status;
use App\Models\TvRating;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Anime::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->sentence;
        $totalEpisodesArray = [10, 12, 24];
        $totalEpisodes = $totalEpisodesArray[array_rand($totalEpisodesArray)];
        $firstAired = Carbon::parse($this->faker->dateTime)->toDate();
        $lastAired = Carbon::parse($firstAired)->addWeeks($totalEpisodes)->toDate();

        $tvRating = TvRating::inRandomOrder()->first();
        if ($tvRating == null) {
            $tvRating = TvRating::factory()->create();
        }

        $mediaType = MediaType::where('type', 'anime')->inRandomOrder()->first();
        if (empty($mediaType)) {
            $mediaType = MediaType::factory()->create();
        }

        $mediaSource = MediaSource::inRandomOrder()->first();
        if (empty($mediaSource)) {
            $mediaSource = MediaSource::factory()->create();
        }

        $status = Status::inRandomOrder()->first();
        if (empty($status)) {
            $status = Status::factory()->create();
        }

        return [
            'slug'              => Str::slug($title),
            'title'             => $title,
            'tagline'           => $this->faker->sentence,
            'synopsis'          => $this->faker->paragraph,
            'tv_rating_id'      => $tvRating,
            'media_type_id'     => $mediaType,
            'media_source_id'   => $mediaSource,
            'status_id'         => $status,
            'network'           => null,
            'producer'          => null,
            'first_aired'       => $firstAired,
            'last_aired'        => $lastAired,
            'runtime'           => $this->faker->numberBetween(10, 25),
            'air_time'          => $this->faker->time('H:i'),
            'air_day'           => DayOfWeek::getRandomValue(),
            'is_nsfw'           => $this->faker->boolean,
            'copyright'         => $this->faker->randomElement(['© ', '℗ ', '® ']) . $this->faker->year . ' ' . $this->faker->company,
        ];
    }
}
