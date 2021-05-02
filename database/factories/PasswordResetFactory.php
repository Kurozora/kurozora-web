<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasswordResetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PasswordReset::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var User $user */
        $user = User::inRandomOrder()->first();

        if ($user == null) {
            $user = User::factory()->create();
        }

        return [
            'email'         => $user->email,
            'token'         => PasswordReset::genToken(),
            'created_at'    => now()
        ];
    }
}
