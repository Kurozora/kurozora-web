<?php

namespace Database\Factories;

use App\Models\ForumSection;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumThreadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ForumThread::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $forumSection = ForumSection::inRandomOrder()->first();
        $user = User::inRandomOrder()->first();

        if ($forumSection == null) {
            $forumSection = ForumSection::factory()->create();
        }
        if ($user == null) {
            $user = User::factory()->create();
        }

        return [
            'section_id'    => $forumSection,
            'user_id'       => $user,
            'ip_address'    => $this->faker->ipv4,
            'title'         => $this->faker->sentence(),
            'content'       => $this->faker->paragraph(),
            'locked'        => $this->faker->boolean()
        ];
    }
}
