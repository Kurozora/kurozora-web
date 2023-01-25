<?php

namespace Database\Seeders;

use App\Models\MediaRelation;
use Illuminate\Database\Seeder;

class MediaRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 10 random media relations
        MediaRelation::factory(10)->create();
    }
}
