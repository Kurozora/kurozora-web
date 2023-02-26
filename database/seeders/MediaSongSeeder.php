<?php

namespace Database\Seeders;

use App\Models\MediaSong;
use Illuminate\Database\Seeder;

class MediaSongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        MediaSong::factory(10)->create();
    }
}
