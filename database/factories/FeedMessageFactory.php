<?php

namespace Database\Factories;

use App\Models\FeedMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedMessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeedMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();

        if ($user == null) {
            $user = User::factory()->create();
        }

        return [
            'user_id'                   => $user,
            'parent_feed_message_id'    => null,
            'body'                      => $this->faker->sentence,
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }
}
