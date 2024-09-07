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
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            RoleSeeder::class,
            StaffRoleSeeder::class,
            CastRoleSeeder::class,
            TvRatingSeeder::class,
            MediaTypeSeeder::class,
            RelationSeeder::class,
            SourceSeeder::class,
            StatusSeeder::class,
            GenreSeeder::class,
//            StudioSeeder::class,
//            AnimeCastSeeder::class,
//            MediaRelationSeeder::class,
//            MediaStaffSeeder::class,
//            SongSeeder::class,
//            MediaSongSeeder::class,
//            PersonSeeder::class,
            UserSeeder::class,
            AchievementSeeder::class,
            AppThemeSeeder::class,
        ]);
    }
}
