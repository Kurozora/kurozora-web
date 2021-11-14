<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeStaff;
use App\Models\KDashboard\AnimeStaff as KAnimeStaff;
use App\Models\Person;
use App\Models\StaffRole;
use App\Scopes\TvRatingScope;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeStaffProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KAnimeStaff[] $kAnimeStaff
     * @return void
     */
    public function process(Collection|array $kAnimeStaff)
    {
        foreach ($kAnimeStaff as $kStaff) {
            $position = match ($kStaff->position->position) {
                '2nd Key Animation' => 'Second Key Animator',
                'Assistant Production Coordinat' => 'Assistant Production Coordinator',
                default => $kStaff->position->position
            };

            $anime = Anime::withoutGlobalScope(new TvRatingScope)->where([
                ['mal_id', $kStaff->anime_id],
            ])->first();
            $person = Person::where([
                ['mal_id', $kStaff->people_id],
            ])->first();
            $staffRole = StaffRole::where([
                ['name', $position]
            ])->first();

            if (empty($staffRole)) {
                dd('Staff is empty', $staffRole, $position, $kStaff->position);
            }

            $animeStaff = AnimeStaff::where([
                ['anime_id', $anime->id],
                ['person_id', $person->id],
                ['staff_role_id', $staffRole->id],
            ])->first();

            if (empty($animeStaff)) {
                AnimeStaff::create([
                    'anime_id' => $anime->id,
                    'person_id' => $person->id,
                    'staff_role_id' => $staffRole->id,
                ]);
            }
        }
    }
}
