<?php

namespace Database\Factories;

use App\Models\PersonalAccessToken;
use App\Models\SessionAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionAttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SessionAttribute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'model_id'          => hash('sha256', $this->faker->macPlatformToken),
            'model_type'        => PersonalAccessToken::class,
            'apn_device_token'  => null,
            'ip_address'        => $this->faker->ipv4,
            'city'              => $this->faker->city,
            'region'            => $this->faker->state,
            'country'           => $this->faker->country,
            'latitude'          => $this->faker->latitude,
            'longitude'         => $this->faker->longitude,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
