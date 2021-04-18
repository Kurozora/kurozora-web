<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Session::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::inRandomOrder()->first();

        if ($user == null) {
            $user = User::factory()->create();
        }

        return [
            'user_id'           => $user,
            'expires_at'        => now()->addDays(90),
            'last_activity_at'  => now(),
            'ip_address'        => $this->faker->ipv4,
            'apn_device_token'  => null,
            'secret'            => Str::random(128),
            'city'              => $this->faker->city,
            'region'            => $this->faker->state,
            'country'           => $this->faker->country,
            'latitude'          => $this->faker->latitude,
            'longitude'         => $this->faker->longitude
        ];
    }
}
