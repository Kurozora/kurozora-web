<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            StudioSeeder::class,
            TvRatingSeeder::class,
            MediaTypeSeeder::class,
            MediaSourceSeeder::class,
            GenreSeeder::class,
            AnimeDummySeeder::class,
            AnimeRelationsSeeder::class,
            UserSeeder::class,
            ForumSectionSeeder::class,
            BadgeSeeder::class,
            ForumThreadSeeder::class,
            ForumReplySeeder::class,
            AppThemeSeeder::class,
            ActorCharacterAnimeSeeder::class,
        ]);
    }
}
