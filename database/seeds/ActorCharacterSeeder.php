<?php

use App\ActorCharacter;
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
        factory(ActorCharacter::class, 10)->create();
    }
}
