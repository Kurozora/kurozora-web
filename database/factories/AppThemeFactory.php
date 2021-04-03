<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\AppTheme;
use Faker\Generator as Faker;
use Salopot\ImageGenerator\ImageProvider;
use Salopot\ImageGenerator\ImageSources\Remote\PicsumPhotosSource;

$factory->define(AppTheme::class, function (Faker $faker) {
    return [
        'name'                                          => $faker->name,

        'global_background_color'                       => $faker->hexColor,
        'global_tinted_background_color'                => $faker->hexColor,
        'global_bar_tint_color'                         => $faker->hexColor,
        'global_bar_title_text_color'                   => $faker->hexColor,
        'global_blur_background_color'                  => $faker->hexColor,
        'global_border_color'                           => $faker->hexColor,
        'global_text_color'                             => $faker->hexColor,
        'global_text_field_background_color'            => $faker->hexColor,
        'global_text_field_text_color'                  => $faker->hexColor,
        'global_text_field_placeholder_text_color'      => $faker->hexColor,
        'global_tint_color'                             => $faker->hexColor,
        'global_tinted_button_text_color'               => $faker->hexColor,
        'global_separator_color'                        => $faker->hexColor,
        'global_separator_color_light'                  => $faker->hexColor,
        'global_sub_text_color'                         => $faker->hexColor,

        'table_view_cell_background_color'              => $faker->hexColor,
        'table_view_cell_title_text_color'              => $faker->hexColor,
        'table_view_cell_sub_text_color'                => $faker->hexColor,
        'table_view_cell_chevron_color'                 => $faker->hexColor,
        'table_view_cell_selected_background_color'     => $faker->hexColor,
        'table_view_cell_selected_title_text_color'     => $faker->hexColor,
        'table_view_cell_selected_sub_text_color'       => $faker->hexColor,
        'table_view_cell_selected_chevron_color'        => $faker->hexColor,
        'table_view_cell_action_default_color'          => $faker->hexColor,
    ];
});

$factory->afterCreating(AppTheme::class, function (AppTheme $theme, Faker $faker) {
    $imageProvider = new ImageProvider($faker);
    $imageProvider->addImageSource(new PicsumPhotosSource($imageProvider));
    $faker->addProvider($imageProvider);

    $theme->addMediaFromBase64($faker->imageGenerator(768, 1024)->getDataUrl())->toMediaCollection('screenshot');
});
