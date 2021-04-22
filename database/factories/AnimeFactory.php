<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Enums\AnimeSource;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use App\Enums\DayOfWeek;
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

        return [
            'title'             => $title,
            'tagline'           => $this->faker->sentence,
            'video_url'         => null,
            'network'           => null,
            'producer'          => null,
            'synopsis'          => $this->faker->paragraph,
            'runtime'           => 25,
            'tv_rating_id'      => TvRating::inRandomOrder()->first(),
            'air_status'        => AnimeStatus::getRandomValue(),
            'type'              => AnimeType::getRandomValue(),
            'adaptation_source' => AnimeSource::getRandomValue(),
            'is_nsfw'           => $this->faker->boolean,
            'anidb_id'          => null,
            'anilist_id'        => null,
            'kitsu_id'          => null,
            'mal_id'            => null,
            'tvdb_id'           => null,
            'slug'              => Str::slug($title),
            'first_aired'       => $firstAired,
            'last_aired'        => $lastAired,
            'air_time'          => $this->faker->time('H:i'),
            'air_day'           => DayOfWeek::getRandomValue(),
            'copyright'         => $this->faker->randomElement(['© ', '℗ ', '® ']) . $this->faker->year . ' ' . $this->faker->company
        ];
    }
}
