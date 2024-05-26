<?php

namespace Tests\API\UserLibrary;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Models\Game;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestGame;
use Tests\Traits\ProvidesTestUser;

class GameLibraryTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestGame;

    /**
     * User can get the watching game in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_watching_game_in_their_library(): void
    {
        // Add a game to the list
        $this->user->track($this->game, UserLibraryStatus::InProgress());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Game,
            'status' => UserLibraryStatus::InProgress,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one game in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the dropped game in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_dropped_game_in_their_library(): void
    {
        // Add a game to the list
        $this->user->track($this->game, UserLibraryStatus::Dropped());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Game,
            'status' => UserLibraryStatus::Dropped,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one game in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the planning game in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_planning_game_in_their_library(): void
    {
        // Add a game to the list
        $this->user->track($this->game, UserLibraryStatus::Planning());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Game,
            'status' => UserLibraryStatus::Planning,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one game in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the completed game in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_completed_game_in_their_library(): void
    {
        // Add a game to the list
        $this->user->track($this->game, UserLibraryStatus::Completed());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Game,
            'status' => UserLibraryStatus::Completed,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one game in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the on-hold game in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_on_hold_game_in_their_library(): void
    {
        // Add a game to the list
        $this->user->track($this->game, UserLibraryStatus::OnHold());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Game,
            'status' => UserLibraryStatus::OnHold,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one game in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User cannot get the game in their library with an invalid status.
     *
     * @return void
     */
    #[Test]
    function user_cannot_get_the_game_in_their_library_with_an_invalid_status(): void
    {
        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Game,
            'status' => 'Invalid Status'
        ]));

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can add game to their watching library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_game_to_their_watching_library(): void
    {
        // Send request to add first game to library
        $model = Game::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::InProgress());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 game in their Watching list
        $count = $this->user->whereTracked(Game::class)
            ->wherePivot('status', UserLibraryStatus::InProgress)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add game to their dropped library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_game_to_their_dropped_library(): void
    {
        // Send request to add first game to library
        $model = Game::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::Dropped());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 game in their Dropped list
        $count = $this->user->whereTracked(Game::class)
            ->wherePivot('status', UserLibraryStatus::Dropped)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add game to their planning library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_game_to_their_planning_library(): void
    {
        // Send request to add first game to library
        $model = Game::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::Planning());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 game in their Planning list
        $count = $this->user->whereTracked(Game::class)
            ->wherePivot('status', UserLibraryStatus::Planning)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add game to their completed library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_game_to_their_completed_library(): void
    {
        // Send request to add first game to library
        $model = Game::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::Completed());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 game in their Completed list
        $count = $this->user->whereTracked(Game::class)
            ->wherePivot('status', UserLibraryStatus::Completed)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add game to their on-hold library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_game_to_their_on_hold_library(): void
    {
        // Send request to add first game to library
        $model = Game::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::OnHold());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 game in their OnHold list
        $count = $this->user->whereTracked(Game::class)
            ->wherePivot('status', UserLibraryStatus::OnHold)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User cannot add game to their library with an invalid status.
     *
     * @return void
     */
    #[Test]
    function user_cannot_add_game_to_their_library_with_an_invalid_status(): void
    {
        // Send request to add first game to library
        $model = Game::first();

        $response = $this->addModelToLibraryAPIRequest($model, null);

        // Check whether the response was unsuccessful
        $this->assertNotNull($response['errors']);
    }

    /**
     * User can delete game from their library.
     *
     * @return void
     */
    #[Test]
    function user_can_delete_game_from_their_library(): void
    {
        // Add a game to the list
        $this->user->track($this->game, UserLibraryStatus::InProgress());

        // Send the request
        $response = $this->auth()->postJson(route('api.me.library.delete', [
            'library' => UserLibraryKind::Game,
            'model_id' => $this->game->getKey(),
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user has no longer tracked the model
        $this->assertTrue($this->user->hasNotTracked($this->game));
    }

    /**
     * User can search in own library.
     *
     * @return void
     */
    #[Test]
    function user_can_search_in_own_library(): void
    {
        // Add a game to the user's list
        $models = Game::factory(20)->create();

        /** @var Game $model */
        foreach ($models as $model) {
            $this->user->track($model, UserLibraryStatus::InProgress());
        }

        // Send the request
        $response = $this->auth()->getJson(route('api.search.index', [
            'scope' => SearchScope::Library,
            'types' => [SearchType::Games],
            'query' => $models->random()->original_title,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the result count is greater than zero
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    /**
     * Sends an API request to add game to a user's library.
     *
     * @param Model                  $model
     * @param UserLibraryStatus|null $status
     *
     * @return TestResponse
     */
    private function addModelToLibraryAPIRequest(Model $model, ?UserLibraryStatus $status): TestResponse
    {
        return $this->auth()->postJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Game,
            'model_id' => $model->getKey(),
            'status' => $status?->key ?? 'Invalid status'
        ]));
    }
}
