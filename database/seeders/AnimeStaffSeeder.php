<?php

namespace Database\Seeders;

use App\Models\AnimeStaff;
use Illuminate\Database\Seeder;

class AnimeStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AnimeStaff::factory(10)->create();
    }
}
