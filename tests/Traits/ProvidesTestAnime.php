<?php

namespace Tests\Traits;

use App\Models\AnimeCast;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\MediaRelation;
use App\Models\Season;
use App\Models\Character;
use App\Models\Person;
use App\Models\TvRating;

trait ProvidesTestAnime
{
    /** @var TvRating $tvRating */
    public TvRating $tvRating;

    /** @var Anime $anime */
    public Anime $anime;

    /** @var Anime $relatedAnime */
    public Anime $relatedAnime;

    /** @var Season $season */
    public Season $season;

    /** @var Episode $episode */
    public Episode $episode;

    /** @var Person $person */
    public Person $person;

    /** @var Character $character */
    public Character $character;

    /** @var AnimeCast $animeCast */
    public AnimeCast $animeCast;

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

        $this->season = Season::factory()->create([
            'anime_id' => $this->anime->id,
        ]);

        $this->episode = Episode::factory()->create([
            'season_id' => $this->season->id,
        ]);

        $this->person = Person::factory()->create();

        $this->character = Character::factory()->create();

        $this->animeCast = AnimeCast::factory()->create([
            'anime_id' => $this->anime->id,
            'character_id' => $this->character->id,
            'person_id' => $this->person->id,
        ]);

        MediaRelation::factory()->create([
            'media_id'      => $this->anime->id,
            'media_type'    => 'anime',
            'related_id'    => $this->relatedAnime->id,
            'related_type'  => 'anime',
        ]);
    }
}
