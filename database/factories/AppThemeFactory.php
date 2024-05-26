<?php

namespace Database\Factories;

use App\Enums\MediaCollection;
use App\Models\AppTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppThemeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppTheme::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,

            'global_background_color' => $this->faker->hexColor,
            'global_tinted_background_color' => $this->faker->hexColor,
            'global_bar_tint_color' => $this->faker->hexColor,
            'global_bar_title_text_color' => $this->faker->hexColor,
            'global_blur_background_color' => $this->faker->hexColor,
            'global_border_color' => $this->faker->hexColor,
            'global_text_color' => $this->faker->hexColor,
            'global_text_field_background_color' => $this->faker->hexColor,
            'global_text_field_text_color' => $this->faker->hexColor,
            'global_text_field_placeholder_text_color' => $this->faker->hexColor,
            'global_tint_color' => $this->faker->hexColor,
            'global_tinted_button_text_color' => $this->faker->hexColor,
            'global_separator_color' => $this->faker->hexColor,
            'global_separator_color_light' => $this->faker->hexColor,
            'global_sub_text_color' => $this->faker->hexColor,

            'table_view_cell_background_color' => $this->faker->hexColor,
            'table_view_cell_title_text_color' => $this->faker->hexColor,
            'table_view_cell_sub_text_color' => $this->faker->hexColor,
            'table_view_cell_chevron_color' => $this->faker->hexColor,
            'table_view_cell_selected_background_color' => $this->faker->hexColor,
            'table_view_cell_selected_title_text_color' => $this->faker->hexColor,
            'table_view_cell_selected_sub_text_color' => $this->faker->hexColor,
            'table_view_cell_selected_chevron_color' => $this->faker->hexColor,
            'table_view_cell_action_default_color' => $this->faker->hexColor,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure(): AppThemeFactory
    {
        return $this->afterCreating(function (AppTheme $theme) {
            $theme->updateImageMedia(MediaCollection::Screenshot(), $this->faker->image(storage_path('framework/testing'), 768, 1024));
        });
    }
}
