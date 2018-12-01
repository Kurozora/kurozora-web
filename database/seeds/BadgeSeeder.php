<?php

use App\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create initial badges
        Badge::create([
            'text'              => 'Founding Father',
            'textColor'         => '#FFFFFF',
            'backgroundColor'   => '#9e1601',
            'description'       => 'This badge is given to the Founding Fathers of Kurozora.'
        ]);
    }
}
