<?php

namespace Database\Seeders;

use App\Models\AppTheme;
use Illuminate\Database\Seeder;

class AppThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 10 random themes
        AppTheme::factory(10)->create();
    }
}
