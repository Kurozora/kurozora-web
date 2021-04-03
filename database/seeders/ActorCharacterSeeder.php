<?php

namespace Database\Seeders;

use App\Models\ActorCharacter;
use Illuminate\Database\Seeder;

class ActorCharacterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ActorCharacter::factory(10)->create();
    }
}
