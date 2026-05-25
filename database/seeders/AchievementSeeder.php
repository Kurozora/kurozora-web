<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Create initial achievement
        Achievement::create([
            'text' => 'Founding Father',
            'textColor' => '#FFFFFF',
            'backgroundColor' => '#9e1601',
            'description' => 'This achievement is given to the Founding Fathers of Kurozora.',
            'is_unlockable' => false,
        ]);
    }
}
