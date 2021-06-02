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
                if (!empty($kCharacter->japanese_name)) {
                    $japaneseName = [
                        'ja' => [
                            'name' => $kCharacter->japanese_name,
                            'about' => '',
                        ],
                    ];
                }

                Character::create(array_merge($japaneseName, [
                    'mal_id' => $kCharacter->id,
                    'nicknames' => empty($kCharacter->nickname) ? null : explode(', ', $kCharacter->nickname),
                    'name' => $kCharacter->name,
                    'about' => $kCharacter->about,
                    'image' => $kCharacter->image_url,
                ]));
            }
        }
    }
}
