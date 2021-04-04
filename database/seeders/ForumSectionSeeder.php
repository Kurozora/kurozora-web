<?php

namespace Database\Seeders;

use App\Models\ForumSection;
use Illuminate\Database\Seeder;

class ForumSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Forum sections to create
        $sectionNames = ['Anime', 'Real Life', 'Memes', 'Art Showcase'];

        // Create the sections
        foreach($sectionNames as $sectName) {
            ForumSection::create([
                'name' => $sectName,
                'locked' => false
            ]);
        }
    }
}
