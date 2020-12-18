<?php

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
        factory(AnimeRelations::class, 10)->create();
    }
}
