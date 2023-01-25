<?php

namespace Database\Seeders;

use App\Models\MediaStaff;
use Illuminate\Database\Seeder;

class MediaStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        MediaStaff::factory(10)->create();
    }
}
