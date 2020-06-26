<?php

use App\Character;
use Illuminate\Database\Seeder;

class CharacterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create initial characters
        factory(Character::class, 10)->create();
    }
}
