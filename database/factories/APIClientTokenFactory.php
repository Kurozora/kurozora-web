<?php

namespace Database\Factories;

use App\Models\APIClientToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class APIClientTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = APIClientToken::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'identifier' => strrev($this->faker->domainName) . '.' . $this->faker->domainWord,
            'description' => $this->faker->sentence,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
