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
            StaffRoleSeeder::class,
            StudioSeeder::class,
            TvRatingSeeder::class,
            MediaTypeSeeder::class,
            RelationSeeder::class,
            SourceSeeder::class,
            StatusSeeder::class,
            GenreSeeder::class,
            AnimeDummySeeder::class,
            MediaRelationSeeder::class,
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
