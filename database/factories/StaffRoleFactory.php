<?php

namespace Database\Factories;

use App\Models\StaffRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StaffRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StaffRole::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
