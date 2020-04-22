<?php

namespace Tests\Traits;

use App\Anime;
use App\AnimeEpisode;
use App\AnimeSeason;

trait ProvidesTestAnime
{
    /** @var Anime $anime */
    public $anime;

	/** @var AnimeSeason $season */
    public $season;

	/** @var AnimeEpisode $episode */
    public $episode;

    /**
     * Creates the test Anime data to be used in tests.
     *
     * @return void
     */
    protected function initializeTestAnime()
    {
        $this->anime = factory(Anime::class)->create();

        $this->season = factory(AnimeSeason::class)->create([
            'anime_id' => $this->anime->id
        ]);

        $this->episode = factory(AnimeEpisode::class)->create([
            'season_id'  => $this->season->id,
        ]);
    }
}
