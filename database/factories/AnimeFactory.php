<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Anime;
use App\Enums\AnimeSource;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use App\Enums\DayOfWeek;
use App\Enums\WatchRating;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Anime::class, function (Faker $faker) {
    $title = $faker->sentence;
    $totalEpisodesArray = [10, 12, 24];
    $totalEpisodes = $totalEpisodesArray[array_rand($totalEpisodesArray)];
    $firstAired = Carbon::parse($faker->dateTime)->toDate();
    $lastAired = Carbon::parse($firstAired)->addWeeks($totalEpisodes)->toDate();

    return [
        'title'             => $title,
        'tagline'           => $faker->sentence,
        'video_url'         => null,
        'network'           => null,
        'producer'          => null,
        'synopsis'          => $faker->paragraph,
        'runtime'           => 25,
        'watch_rating'      => WatchRating::getRandomValue(),
        'air_status'        => AnimeStatus::getRandomValue(),
        'type'              => AnimeType::getRandomValue(),
        'adaptation_source' => AnimeSource::getRandomValue(),
        'is_nsfw'           => $faker->boolean,
        'anidb_id'          => null,
        'anilist_id'        => null,
        'kitsu_id'          => null,
        'mal_id'            => null,
        'tvdb_id'           => null,
        'slug'              => Str::slug($title),
        'first_aired'       => $firstAired,
        'last_aired'        => $lastAired,
        'air_time'          => $faker->time('H:i'),
        'air_day'           => DayOfWeek::getRandomValue(),
        'copyright'         => $faker->randomElement(['© ', '℗ ', '® ']) . $faker->year . ' ' . $faker->company
    ];
});
