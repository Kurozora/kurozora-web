<?php

namespace Database\Seeders;

use App\Models\AnimeCast;
use Illuminate\Database\Seeder;

class AnimeCastSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        AnimeCast::factory(10)->create();
    }
}
