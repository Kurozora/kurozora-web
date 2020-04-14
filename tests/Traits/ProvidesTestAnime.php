<?php

namespace Tests\API\Traits;

use App\Anime;
use App\AnimeEpisode;
use App\AnimeSeason;

trait ProvidesTestAnime {
    /** @var Anime $anime */
	public Anime $anime;

	/** @var AnimeSeason $season */
	public AnimeSeason $season;

	/** @var AnimeEpisode $episode */
	public AnimeEpisode $episode;

    /**
     * Creates the test episode to be used in tests.
     *
     * @return void
     */
    protected function initializeTestAnime() {
    	$this->anime = factory(Anime::class)->create();
    	$this->season = factory(AnimeSeason::class)->create([
    		'anime_id' => $this->anime->id
	    ]);
        $this->episode = factory(AnimeEpisode::class)->create([
            'season_id'  => $this->season->id,
        ]);
    }
}
