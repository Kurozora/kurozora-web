<?php

namespace App\Services;

use App\Models\Character;
use App\Models\KDashboard\Character as KCharacter;
use Illuminate\Database\Eloquent\Collection;

class ImportCharacterProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KCharacter[] $kCharacters
     * @return void
     */
    public function process(Collection|array $kCharacters)
    {
        foreach ($kCharacters as $kCharacter) {
            $character = Character::where([
                ['mal_id', $kCharacter->id],
            ])->first();

            if (empty($character)) {
                $japaneseName = [];
                if (!empty(trim($kCharacter->japanese_name))) {
                    $japaneseName = [
                        'ja' => [
                            'name' => trim($kCharacter->japanese_name),
                            'about' => null,
                        ],
                    ];
                }

                Character::create(array_merge($japaneseName, [
                    'mal_id' => $kCharacter->id,
                    'nicknames' => empty($kCharacter->nickname) ? null : explode(', ', $kCharacter->nickname),
                    'name' => trim($kCharacter->name),
                    'about' => trim($kCharacter->about) ?: null,
                    'image' => empty($kCharacter->image_url) ? null : $kCharacter->image_url,
                ]));
            }
        }
    }
}
