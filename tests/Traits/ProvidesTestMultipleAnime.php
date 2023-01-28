<?php

namespace Tests\Traits;

use App\Models\Anime;

trait ProvidesTestMultipleAnime
{
    /** @var Anime $anime */
    public Anime $anime;

    /** @var int[] $malIds */
    public array $malIds = [
        6076,
        4469,
        3269,
        2928,
        1143,
        454,
        21,
    ];

    /**
     * Creates the test Anime data to be used in tests.
     *
     * @return void
     */
    protected function setupProvidesTestMultipleAnime(): void
    {
        foreach ($this->malIds as $malId) {
            Anime::factory()->create([
                'mal_id' => $malId
            ]);
        }
    }
}
