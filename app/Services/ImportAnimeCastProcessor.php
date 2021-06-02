<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\KDashboard\AnimeCharacter as KAnimeCast;
use App\Models\Language;
use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeCastProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KAnimeCast[] $kAnimeCasts
     * @return void
     */
    public function process(Collection|array $kAnimeCasts)
    {
        foreach ($kAnimeCasts as $kAnimeCast) {
            $kCastRole = match ($kAnimeCast->role) {
                'main' => 'Protagonist',
                default => 'Supporting Character'
            };
            $kLanguage = $kAnimeCast->language ? match ($kAnimeCast->language->language) {
                'Mandarin' => 'Chinese',
                'Brazilian' => 'Portuguese',
                default => $kAnimeCast->language->language
            } : null;

            $animeId = Anime::firstWhere('mal_id', $kAnimeCast->anime_id)->id;
            $characterId = Character::firstWhere('mal_id' , $kAnimeCast->character_id)->id;
            $personId = $kAnimeCast->people_id ? Person::firstWhere('mal_id', $kAnimeCast->people_id)->id : null;
            $castRoleId = CastRole::firstWhere('name', $kCastRole)->id;
            $languageId = $kLanguage ? Language::firstWhere('name', $kLanguage)->id : null;

            $animeCast = AnimeCast::where([
                ['anime_id', $animeId],
                ['character_id', $characterId],
                ['person_id', $personId],
                ['cast_role_id', $castRoleId],
                ['language_id', $languageId],
            ])->first();

            if (empty($animeCast)) {
                AnimeCast::create([
                    'anime_id' => $animeId,
                    'character_id' => $characterId,
                    'person_id' => $personId,
                    'cast_role_id' => $castRoleId,
                    'language_id' => $languageId,
                ]);
            }
        }
    }
}
