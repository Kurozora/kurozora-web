<?php

namespace Database\Seeders;

use App\Models\AnimeSong;
use Illuminate\Database\Seeder;

class AnimeSongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        AnimeSong::factory(10)->create();
    }
}
