<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\MediaStaff;
use App\Models\KDashboard\AnimeStaff as KMediaStaff;
use App\Models\Person;
use App\Models\StaffRole;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeStaffProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KMediaStaff[] $kMediaStaff
     * @return void
     */
    public function process(Collection|array $kMediaStaff): void
    {
        foreach ($kMediaStaff as $kStaff) {
            $position = match ($kStaff->position->position) {
                '2nd Key Animation' => 'Second Key Animator',
                'Assistant Production Coordinat' => 'Assistant Production Coordinator',
                default => $kStaff->position->position
            };

            $anime = Anime::withoutGlobalScopes()
                ->where([
                    ['mal_id', $kStaff->anime_id],
                ])->first();
            $person = Person::where([
                ['mal_id', $kStaff->people_id],
            ])->first();
            $staffRole = StaffRole::where([
                ['name', $position]
            ])->first();

            if (empty($staffRole)) {
                info('Staff role is empty. Position: ' . $position . '/' . $kStaff->position);
            }

            $mediaStaff = MediaStaff::where([
                ['model_type', '=', $anime->getMorphClass()],
                ['model_id', '=', $anime->id],
                ['person_id', $person->id],
                ['staff_role_id', $staffRole->id],
            ])->first();

            if (empty($mediaStaff)) {
                MediaStaff::create([
                    'model_type' => $anime->getMorphClass(),
                    'model_id' => $anime->id,
                    'person_id' => $person->id,
                    'staff_role_id' => $staffRole->id,
                ]);
            }
        }
    }
}
