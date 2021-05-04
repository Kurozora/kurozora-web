<?php

namespace Tests\Traits;

use App\Models\Actor;
use App\Models\ActorCharacter;
use App\Models\ActorCharacterAnime;
use App\Models\Anime;
use App\Models\AnimeEpisode;
use App\Models\MediaRelation;
use App\Models\AnimeSeason;
use App\Models\Character;
use App\Models\TvRating;

trait ProvidesTestAnime
{
    /** @var TvRating $tvRating */
    public TvRating $tvRating;

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
        $this->tvRating = TvRating::factory()->create();

        $this->anime = Anime::factory()->create();

        $this->relatedAnime = Anime::factory()->create();

        $this->season = AnimeSeason::factory()->create([
            'anime_id' => $this->anime->id,
        ]);

        $this->episode = AnimeEpisode::factory()->create([
            'season_id' => $this->season->id,
        ]);

        $this->actor = Actor::factory()->create();

        $this->character = Character::factory()->create();

        $this->actorCharacter = ActorCharacter::factory()->create([
            'actor_id' => $this->actor->id,
            'character_id' => $this->character->id,
        ]);

        ActorCharacterAnime::factory()->create([
            'actor_character_id' => $this->actorCharacter->id,
            'anime_id' => $this->anime->id,
        ]);

        MediaRelation::factory()->create([
            'media_id'      => $this->anime->id,
            'media_type'    => 'anime',
            'related_id'    => $this->relatedAnime->id,
            'related_type'  => 'anime',
        ]);
    }
}
