<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\MediaStaff;
use App\Models\Person;
use App\Models\StaffRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaStaffFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MediaStaff::class;

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
            'model_type'    => $anime->getMorphClass(),
            'model_id'      => $anime->id,
            'person_id'     => $person->id,
            'staff_role_id' => $staffRole->id,
        ];
    }
}
