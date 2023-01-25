<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
use App\Models\Manga;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class MangaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Manga::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $jaFaker = \Faker\Factory::create('ja_JP');
        $title = $this->faker->sentence;
        $totalChaptersArray = [10, 12, 24];
        $totalChapters = $totalChaptersArray[array_rand($totalChaptersArray)];
        $startedAt = Carbon::parse($this->faker->dateTime)->toDate();
        $endedAt = Carbon::parse($startedAt)->addWeeks($totalChapters)->toDate();

        $tvRating = TvRating::inRandomOrder()->first();
        if ($tvRating == null) {
            $tvRating = TvRating::factory()->create();
        }

        $mediaType = MediaType::where('type', 'manga')
            ->inRandomOrder()
            ->first();
        if (empty($mediaType)) {
            $mediaType = MediaType::factory()->create([
                'type'  => 'manga'
            ]);
        }

        $source = Source::inRandomOrder()->first();
        if (empty($source)) {
            $source = Source::factory()->create();
        }

        $status = Status::where('type', 'manga')
            ->inRandomOrder()
            ->first();
        if (empty($status)) {
            $status = Status::factory()->create([
                'type'  => 'manga'
            ]);
        }

        return [
            'slug'              => str($title)->slug(),
            'original_title'    => $title,
            'title'             => $title,
            'synopsis'          => $this->faker->realText(),
            'tagline'           => $this->faker->sentence,
            'ja'                => [
                'title'         => $jaFaker->sentence,
                'synopsis'      => $jaFaker->realText(),
                'tagline'       => $jaFaker->sentence,
            ],
            'synonym_titles'    => $this->faker->sentences(),
            'tv_rating_id'      => $tvRating,
            'media_type_id'     => $mediaType,
            'source_id'         => $source,
            'status_id'         => $status,
            'duration'          => $this->faker->numberBetween(10, 25),
            'publication_time'  => $this->faker->time(),
            'publication_day'   => DayOfWeek::getRandomValue(),
            'is_nsfw'           => $this->faker->boolean,
            'copyright'         => $this->faker->randomElement(['© ', '℗ ', '® ']) . $this->faker->year . ' ' . $this->faker->company,
            'started_at'        => $startedAt,
            'ended_at'          => $endedAt,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
