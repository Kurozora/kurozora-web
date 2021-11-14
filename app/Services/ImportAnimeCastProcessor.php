<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\KDashboard\AnimeCharacter as KAnimeCast;
use App\Models\Language;
use App\Models\Person;
use App\Scopes\TvRatingScope;
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
            $animeId = Anime::withoutGlobalScope(new TvRatingScope)->firstWhere('mal_id', $kAnimeCast->anime_id)->id;
            $characterId = Character::firstWhere('mal_id' , $kAnimeCast->character_id)?->id;
            $personId = Person::firstWhere('mal_id', $kAnimeCast->people_id)?->id;

            if (!empty($characterId)) {
                $animeCast = AnimeCast::where([
                    ['anime_id', $animeId],
                    ['character_id', $characterId],
                    ['person_id', $personId],
                ])->first();

                if (empty($animeCast)) {
                    $kCastRole = match ($kAnimeCast->role) {
                        'main' => 'Protagonist',
                        default => 'Supporting Character'
                    };
                    $kLanguage = match ($kAnimeCast->language?->language) {
                        'Mandarin' => 'Chinese',
                        'Brazilian' => 'Portuguese',
                        default => $kAnimeCast->language?->language
                    };

                    $castRoleId = CastRole::firstWhere('name', $kCastRole)->id;
                    $languageId = Language::firstWhere('name', $kLanguage)?->id;

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
}
