<?php

namespace Database\Factories;

use App\Enums\StudioType;
use App\Models\Studio;
use App\Models\TvRating;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Studio::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $tvRating = TvRating::inRandomOrder()->first();
        if ($tvRating == null) {
            $tvRating = TvRating::factory()->create();
        }

        $name = $this->faker->unique()->company;

        return [
            'tv_rating_id'  => $tvRating,
            'slug'          => str($name)->slug(),
            'type'          => StudioType::getRandomValue(),
            'name'          => $name,
            'about'         => $this->faker->realText(),
            'address'       => $this->faker->address(),
            'founded'       => $this->faker->date(),
            'website_urls'  => $this->faker->randomElement([[$this->faker->url], null]),
            'is_nsfw'       => $this->faker->boolean,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
