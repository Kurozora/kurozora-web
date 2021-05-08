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
            LanguageSeeder::class,
            RoleSeeder::class,
            StaffRoleSeeder::class,
            CastRoleSeeder::class,
            StudioSeeder::class,
            TvRatingSeeder::class,
            MediaTypeSeeder::class,
            RelationSeeder::class,
            SourceSeeder::class,
            StatusSeeder::class,
            GenreSeeder::class,
            AnimeDummySeeder::class,
            AnimeCastSeeder::class,
            MediaRelationSeeder::class,
            SongSeeder::class,
            AnimeSongSeeder::class,
            UserSeeder::class,
            BadgeSeeder::class,
            ForumSectionSeeder::class,
            ForumThreadSeeder::class,
            ForumReplySeeder::class,
            AppThemeSeeder::class,
        ]);
    }
}
