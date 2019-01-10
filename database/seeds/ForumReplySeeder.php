<?php

use App\ForumReply;
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
        factory(ForumReply::class, 200)->create();
    }
}
