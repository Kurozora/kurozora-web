<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id'            => $this->faker->uuid,
            'user_id'       => User::factory()->create()->id,
            'ip_address'    => $this->faker->ipv4(),
            'user_agent'    => $this->faker->userAgent,
            'payload'       => base64_encode($this->faker->sentence),
            'last_activity' => now()
        ];
    }
}
