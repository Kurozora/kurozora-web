<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\AnimeStaff;
use App\Models\Person;
use App\Models\StaffRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeStaffFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AnimeStaff::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $anime = Anime::inRandomOrder(mt_rand(1, 999))->first();
        $person = Person::inRandomOrder(mt_rand(1, 999))->first();
        $staffRole = StaffRole::inRandomOrder(mt_rand(1, 999))->first();

        if ($anime == null) {
            $anime = Anime::factory()->create();
        }
        if ($person == null) {
            $person = Person::factory()->create();
        }
        if ($staffRole == null) {
            $staffRole = StaffRole::factory()->create();
        }

        return [
            'anime_id'      => $anime,
            'person_id'     => $person,
            'staff_role_id' => $this->faker->randomElement([$staffRole, null])
        ];
    }
}
