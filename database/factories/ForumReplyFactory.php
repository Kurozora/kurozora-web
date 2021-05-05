<?php

namespace Database\Factories;

use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumReplyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ForumReply::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $forumThread = ForumThread::inRandomOrder()->first();
        $user = User::inRandomOrder()->first();

        if ($forumThread == null) {
            $forumThread = ForumThread::factory()->create();
        }
        if ($user == null) {
            $user = User::factory()->create();
        }

        return [
            'thread_id'     => $forumThread,
            'user_id'       => $user,
            'ip_address'    => $this->faker->ipv4,
            'content'       => $this->faker->paragraph(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
