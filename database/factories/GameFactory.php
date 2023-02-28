<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Models\Game;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Game::class;

    public function definition(): array
    {
        $jaFaker = \Faker\Factory::create('ja_JP');
        $title = $this->faker->sentence;
        $publishedAt = Carbon::parse($this->faker->dateTime)->toDate();

        $tvRating = TvRating::inRandomOrder()->first();
        if ($tvRating == null) {
            $tvRating = TvRating::factory()->create();
        }

        $mediaType = MediaType::where('type', 'game')->inRandomOrder()->first();
        if (empty($mediaType)) {
            $mediaType = MediaType::factory()->create([
                'type'  => 'game'
            ]);
        }

        $source = Source::inRandomOrder()->first();
        if (empty($source)) {
            $source = Source::factory()->create();
        }

        $status = Status::where('type', 'game')->inRandomOrder()->first();
        if (empty($status)) {
            $status = Status::factory()->create([
                'type'  => 'game'
            ]);
        }

        return [
            'slug'                  => str($title)->slug(),
            'original_title'        => $title,
            'title'                 => $title,
            'synopsis'              => $this->faker->realText(),
            'tagline'               => $this->faker->sentence,
            'ja'                    => [
                'title'             => $jaFaker->sentence,
                'synopsis'          => $jaFaker->realText(),
                'tagline'           => $jaFaker->sentence,
            ],
            'synonym_titles'        => $this->faker->sentences(),
            'tv_rating_id'          => $tvRating,
            'media_type_id'         => $mediaType,
            'source_id'             => $source,
            'status_id'             => $status,
            'duration'              => $this->faker->numberBetween(30, 60),
            'publication_day'       => DayOfWeek::getRandomValue(),
            'publication_season'    => SeasonOfYear::getRandomValue(),
            'is_nsfw'               => $this->faker->boolean,
            'copyright'             => $this->faker->randomElement(['© ', '℗ ', '® ']) . $this->faker->year . ' ' . $this->faker->company,
            'edition_count'         => $this->faker->randomNumber(),
            'view_count'            => $this->faker->randomNumber(),
            'published_at'          => $publishedAt,
            'created_at'            => now(),
            'updated_at'            => now(),
        ];
    }
}
