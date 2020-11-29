<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\AppTheme;
use Faker\Generator as Faker;

$factory->define(AppTheme::class, function (Faker $faker) {
    return [
        'name'                                          => $faker->name,

        'global_background_color'                       => $faker->hexcolor,
        'global_tinted_background_color'                => $faker->hexcolor,
        'global_bar_tint_color'                         => $faker->hexcolor,
        'global_bar_title_text_color'                   => $faker->hexcolor,
        'global_blur_background_color'                  => $faker->hexcolor,
        'global_border_color'                           => $faker->hexcolor,
        'global_text_color'                             => $faker->hexcolor,
        'global_text_field_background_color'            => $faker->hexcolor,
        'global_text_field_text_color'                  => $faker->hexcolor,
        'global_text_field_placeholder_text_color'      => $faker->hexcolor,
        'global_tint_color'                             => $faker->hexcolor,
        'global_tinted_button_text_color'               => $faker->hexcolor,
        'global_separator_color'                        => $faker->hexcolor,
        'global_separator_color_light'                  => $faker->hexcolor,
        'global_sub_text_color'                         => $faker->hexcolor,

        'table_view_cell_background_color'              => $faker->hexcolor,
        'table_view_cell_title_text_color'              => $faker->hexcolor,
        'table_view_cell_sub_text_color'                => $faker->hexcolor,
        'table_view_cell_chevron_color'                 => $faker->hexcolor,
        'table_view_cell_selected_background_color'     => $faker->hexcolor,
        'table_view_cell_selected_title_text_color'     => $faker->hexcolor,
        'table_view_cell_selected_sub_text_color'       => $faker->hexcolor,
        'table_view_cell_selected_chevron_color'        => $faker->hexcolor,
        'table_view_cell_action_default_color'          => $faker->hexcolor,
    ];
});

$factory->afterCreating(AppTheme::class, function (AppTheme $theme, Faker $faker) {
    $theme->addMediaFromUrl($faker->imageUrl(768, 1024, true, false))->toMediaCollection('screenshot');
});
