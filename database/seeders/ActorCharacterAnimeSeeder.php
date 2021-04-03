<?php

namespace Database\Seeders;

use App\Models\ActorCharacterAnime;
use Illuminate\Database\Seeder;

class ActorCharacterAnimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ActorCharacterAnime::class, 10)->create();
    }
}
