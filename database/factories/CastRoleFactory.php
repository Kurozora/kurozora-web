<?php

namespace Database\Factories;

use App\Models\CastRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class CastRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CastRole::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->name,
            'description'   => $this->faker->words(3, true),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
