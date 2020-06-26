<?php

use App\ActorAnimeCharacter;
use Illuminate\Database\Seeder;

class ActorAnimeCharacterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ActorAnimeCharacter::class, 10)->create();
    }
}
