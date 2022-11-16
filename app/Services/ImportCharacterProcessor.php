<?php

namespace App\Services;

use App\Enums\MediaCollection;
use App\Models\Character;
use App\Models\KDashboard\Character as KCharacter;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Log;

class ImportCharacterProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KCharacter[] $kCharacters
     * @return void
     */
    public function process(Collection|array $kCharacters): void
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
                ]));
            }

            // Download poster when available and if not already present
            if (!empty($kCharacter->image_url) && !empty($character) && empty($character->getFirstMedia(MediaCollection::Profile))) {
                try {
                    $character->updateImageMedia(MediaCollection::Profile(), $kCharacter->image_url, $character->name);
                } catch (Exception $e) {
                    Log::info('character:' . $e->getMessage());
                    Log::info('character mal id: ' . $kCharacter->id);
                }
            }
        }
    }
}
