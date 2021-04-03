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
    public function run()
    {
        // 10 random themes
        factory(AppTheme::class, 10)->create();
    }
}
