<?php

namespace App\Services;

use App\Models\KDashboard\ProducerMagazine as KStudio;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Collection;

class ImportStudioProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KStudio[] $kStudios
     * @return void
     */
    public function process(Collection|array $kStudios)
    {
        foreach ($kStudios as $kStudio) {
            $studio = Studio::where([
                ['mal_id', $kStudio->id],
                ['type', $kStudio->type],
                ['name', $kStudio->name]
            ])->first();

            if (empty($studio)) {
                Studio::create([
                    'mal_id' => $kStudio->id,
                    'type' => $kStudio->type,
                    'name' => $kStudio->name,
                ]);
            }
        }
    }
}
