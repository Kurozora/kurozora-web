<?php

namespace Tests\Traits;

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\Character;
use App\Models\Episode;
use App\Models\MediaRelation;
use App\Models\Person;
use App\Models\Season;
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
    protected function setupProvidesTestAnime(): void
    {
        // Create a tv rating
        $this->tvRating = TvRating::factory()
            ->create();

        // Create an anime
        $this->anime = Anime::factory()
            ->create();

        // Create a related anime
        $this->relatedAnime = Anime::factory()
            ->create();

        // Create a season
        $this->season = Season::factory()
            ->create([
                'anime_id' => $this->anime->id,
            ]);

        // Create an episode and connect to season
        $this->episode = Episode::factory()
            ->create([
                'season_id' => $this->season->id,
            ]);

        // Create a person
        $this->person = Person::factory()
            ->create();

        // Create a character
        $this->character = Character::factory()
            ->create();

        // Create a cast form person and character
        $this->animeCast = AnimeCast::factory()
            ->create([
                'anime_id' => $this->anime->id,
                'character_id' => $this->character->id,
                'person_id' => $this->person->id,
                'language_id' => 73 // only japanese cast for now
            ]);

        // Create a relationship between main and related anime
        MediaRelation::factory()
            ->create([
                'model_id' => $this->anime->id,
                'model_type' => Anime::class,
                'related_id' => $this->relatedAnime->id,
                'related_type' => Anime::class,
            ]);
    }
}
