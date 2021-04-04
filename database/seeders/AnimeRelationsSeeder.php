<?php

namespace Database\Seeders;

use App\Models\AnimeRelations;
use Illuminate\Database\Seeder;

class AnimeRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 10 random anime-anime
        AnimeRelations::factory(10)->create();
    }
}
