<?php

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
            AnimesTableDummySeeder::class,
            UsersTableSeeder::class,
            ForumSectionSeeder::class,
            BadgeSeeder::class,
            ForumThreadSeeder::class,
            ForumReplySeeder::class,
            GenreSeeder::class,
            AppThemeSeeder::class,
            ActorCharacterAnimeSeeder::class,
        ]);
    }
}
