<?php

namespace Database\Factories;

use App\Models\Theme;
use App\Models\TvRating;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ThemeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Theme::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'mal_id' => $this->faker->randomNumber(),
            'tv_rating_id' => TvRating::factory()->create(),
            'slug' => $this->faker->slug(),
            'name' => $this->faker->name(),
            'color' => $this->faker->hexColor(),
            'background_color_1' => $this->faker->hexColor(),
            'background_color_2' => $this->faker->hexColor(),
            'text_color_1' => $this->faker->hexColor(),
            'text_color_2' => $this->faker->hexColor(),
            'description' => $this->faker->text(),
            'is_nsfw' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
