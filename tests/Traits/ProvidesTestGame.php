<?php

namespace Tests\Traits;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\GameCast;
use App\Models\MediaRelation;
use App\Models\Person;
use App\Models\Season;
use App\Models\TvRating;

trait ProvidesTestGame
{
    /** @var TvRating $tvRating */
    public TvRating $tvRating;

    /** @var Game $game */
    public Game $game;

    /** @var Game $relatedGame */
    public Game $relatedGame;

    /** @var Season $season */
    public Season $season;

    /** @var Episode $episode */
    public Episode $episode;

    /** @var Person $person */
    public Person $person;

    /** @var Character $character */
    public Character $character;

    /** @var GameCast $gameCast */
    public GameCast $gameCast;

    /**
     * Creates the test Game data to be used in tests.
     *
     * @return void
     */
    protected function setupProvidesTestGame(): void
    {
        // Create a tv rating
        $this->tvRating = TvRating::factory()
            ->create();

        // Create a game
        $this->game = Game::factory()
            ->create();

        // Create a related game
        $this->relatedGame = Game::factory()
            ->create();

        // Create a person
        $this->person = Person::factory()
            ->create();

        // Create a character
        $this->character = Character::factory()
            ->create();

        // Create a cast form person and character
        $this->gameCast = GameCast::factory()
            ->create([
                'game_id' => $this->game->id,
                'character_id' => $this->character->id,
                'person_id' => $this->person->id,
                'language_id' => 73 // only japanese cast for now
            ]);

        // Create a relationship between main and related game
        MediaRelation::factory()
            ->create([
                'model_id' => $this->game->id,
                'model_type' => $this->game->getMorphClass(),
                'related_id' => $this->relatedGame->id,
                'related_type' => $this->relatedGame->getMorphClass(),
            ]);
    }
}
