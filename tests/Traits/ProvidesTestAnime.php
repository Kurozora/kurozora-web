<?php

namespace Tests\Traits;

use App\Actor;
use App\ActorCharacter;
use App\ActorCharacterAnime;
use App\Anime;
use App\AnimeEpisode;
use App\AnimeRelations;
use App\AnimeSeason;
use App\Character;

trait ProvidesTestAnime
{
    /** @var Anime $anime */
    public $anime;

    /** @var Anime $relatedAnime */
    public $relatedAnime;

	/** @var AnimeSeason $season */
    public $season;

	/** @var AnimeEpisode $episode */
    public $episode;

    /** @var Actor $actor */
    public $actor;

    /** @var Character $character */
    public $character;

    /** @var ActorCharacter $actorCharacter */
    public $actorCharacter;

    /**
     * Creates the test Anime data to be used in tests.
     *
     * @return void
     */
    protected function initializeTestAnime()
    {
        $this->anime = factory(Anime::class)->create();

        $this->relatedAnime = factory(Anime::class)->create();

        $this->season = factory(AnimeSeason::class)->create([
            'anime_id' => $this->anime->id
        ]);

        $this->episode = factory(AnimeEpisode::class)->create([
            'season_id' => $this->season->id,
        ]);

        $this->actor = factory(Actor::class)->create();

        $this->character = factory(Character::class)->create();

        $this->actorCharacter = factory(ActorCharacter::class)->create([
            'actor_id' => $this->actor->id,
            'character_id' => $this->character->id
        ]);

        factory(ActorCharacterAnime::class)->create([
            'actor_character_id' => $this->actorCharacter->id,
            'anime_id' => $this->anime->id
        ]);

        factory(AnimeRelations::class)->create([
            'anime_id'          => $this->anime->id,
            'related_anime_id'  => $this->relatedAnime->id
        ]);
    }
}
