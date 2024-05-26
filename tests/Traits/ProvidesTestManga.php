<?php

namespace Tests\Traits;

use App\Models\Character;
use App\Models\Manga;
use App\Models\MangaCast;
use App\Models\MediaRelation;
use App\Models\Person;
use App\Models\TvRating;

trait ProvidesTestManga
{
    /** @var TvRating $tvRating */
    public TvRating $tvRating;

    /** @var Manga $manga */
    public Manga $manga;

    /** @var Manga $relatedManga */
    public Manga $relatedManga;

    /** @var Person $person */
    public Person $person;

    /** @var Character $character */
    public Character $character;

    /** @var MangaCast $mangaCast */
    public MangaCast $mangaCast;

    /**
     * Creates the test Manga data to be used in tests.
     *
     * @return void
     */
    protected function setupProvidesTestManga(): void
    {
        // Create a tv rating
        $this->tvRating = TvRating::factory()
            ->create();

        // Create a manga
        $this->manga = Manga::factory()
            ->create();

        // Create a related manga
        $this->relatedManga = Manga::factory()
            ->create();

        // Create a person
        $this->person = Person::factory()
            ->create();

        // Create a character
        $this->character = Character::factory()
            ->create();

        // Create a cast form person and character
        $this->mangaCast = MangaCast::factory()
            ->create([
                'manga_id' => $this->manga->id,
                'character_id' => $this->character->id,
            ]);

        // Create a relationship between main and related manga
        MediaRelation::factory()
            ->create([
                'model_id' => $this->manga->id,
                'model_type' => Manga::class,
                'related_id' => $this->relatedManga->id,
                'related_type' => Manga::class,
            ]);
    }
}
