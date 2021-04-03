<?php

namespace Database\Seeders;

use App\Models\ForumReply;
use Illuminate\Database\Seeder;

class ForumReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 200 random replies
        ForumReply::factory(200)->create();
    }
}
