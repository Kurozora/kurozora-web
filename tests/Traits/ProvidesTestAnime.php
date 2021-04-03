<?php

namespace Tests\Traits;

use App\Models\Actor;
use App\Models\ActorCharacter;
use App\Models\ActorCharacterAnime;
use App\Models\Anime;
use App\Models\AnimeEpisode;
use App\Models\AnimeRelations;
use App\Models\AnimeSeason;
use App\Models\Character;

trait ProvidesTestAnime
{
    /** @var Anime $anime */
    public Anime $anime;

    /** @var Anime $relatedAnime */
    public Anime $relatedAnime;

    /** @var AnimeSeason $season */
    public AnimeSeason $season;

    /** @var AnimeEpisode $episode */
    public AnimeEpisode $episode;

    /** @var Actor $actor */
    public Actor $actor;

    /** @var Character $character */
    public Character $character;

    /** @var ActorCharacter $actorCharacter */
    public ActorCharacter $actorCharacter;

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
