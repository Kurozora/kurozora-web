<?php

namespace App\Services;

use App\Models\Manga;
use App\Models\MediaStaff;
use App\Models\KDashboard\PeopleManga as KMediaStaff;
use App\Models\Person;
use App\Models\StaffRole;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaStaffProcessor
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

            $manga = Manga::withoutGlobalScopes()
                ->where([
                    ['mal_id', $kStaff->manga_id],
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
                ['model_type', '=', $manga->getMorphClass()],
                ['model_id', '=', $manga->id],
                ['person_id', $person->id],
                ['staff_role_id', $staffRole->id],
            ])->first();

            if (empty($mediaStaff)) {
                MediaStaff::create([
                    'model_type' => $manga->getMorphClass(),
                    'model_id' => $manga->id,
                    'person_id' => $person->id,
                    'staff_role_id' => $staffRole->id,
                ]);
            }
        }
    }
}
