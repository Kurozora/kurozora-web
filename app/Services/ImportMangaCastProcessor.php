<?php

namespace App\Services;

use App\Models\Manga;
use App\Models\MangaCast;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\KDashboard\MangaCharacter as KMangaCast;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaCastProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KMangaCast[] $kMangaCasts
     * @return void
     */
    public function process(Collection|array $kMangaCasts): void
    {
        foreach ($kMangaCasts as $kMangaCast) {
            $mangaId = Manga::withoutGlobalScopes()->firstWhere('mal_id', $kMangaCast->manga_id)->id;
            $characterId = Character::withoutGlobalScopes()->firstWhere('mal_id' , $kMangaCast->character_id)?->id;

            if (!empty($characterId)) {
                $mangaCast = MangaCast::firstWhere([
                    ['manga_id', $mangaId],
                    ['character_id', $characterId],
                ]);

                if (empty($mangaCast)) {
                    $kCastRole = match ($kMangaCast->role) {
                        'main' => 'Protagonist',
                        default => 'Supporting Character'
                    };

                    $castRoleId = CastRole::firstWhere('name', $kCastRole)->id;

                    MangaCast::create([
                        'manga_id' => $mangaId,
                        'character_id' => $characterId,
                        'cast_role_id' => $castRoleId,
                    ]);
                }
            }
        }
    }
}
