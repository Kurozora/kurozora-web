<?php

namespace Tests\API\UserLibrary;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

class AnimeLibraryTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestAnime;

    /**
     * User can get the watching anime in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_watching_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::InProgress());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Anime,
            'status' => UserLibraryStatus::InProgress,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the dropped anime in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_dropped_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::Dropped());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Anime,
            'status' => UserLibraryStatus::Dropped,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the planning anime in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_planning_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::Planning());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Anime,
            'status' => UserLibraryStatus::Planning,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the completed anime in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_completed_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::Completed());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Anime,
            'status' => UserLibraryStatus::Completed,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the on-hold anime in their library.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_on_hold_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::OnHold());

        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Anime,
            'status' => UserLibraryStatus::OnHold,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User cannot get the anime in their library with an invalid status.
     *
     * @return void
     */
    #[Test]
    function user_cannot_get_the_anime_in_their_library_with_an_invalid_status(): void
    {
        // Send the request
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Anime,
            'status' => 'Invalid Status'
        ]));

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can add anime to their watching library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_anime_to_their_watching_library(): void
    {
        // Send request to add first anime to library
        $model = Anime::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::InProgress());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Watching list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::InProgress)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their dropped library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_anime_to_their_dropped_library(): void
    {
        // Send request to add first anime to library
        $model = Anime::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::Dropped());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Dropped list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::Dropped)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their planning library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_anime_to_their_planning_library(): void
    {
        // Send request to add first anime to library
        $model = Anime::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::Planning());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Planning list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::Planning)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their completed library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_anime_to_their_completed_library(): void
    {
        // Send request to add first anime to library
        $model = Anime::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::Completed());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Completed list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::Completed)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their on-hold library.
     *
     * @return void
     */
    #[Test]
    function user_can_add_anime_to_their_on_hold_library(): void
    {
        // Send request to add first anime to library
        $model = Anime::first();

        $response = $this->addModelToLibraryAPIRequest($model, UserLibraryStatus::OnHold());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their OnHold list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::OnHold)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User cannot add anime to their library with an invalid status.
     *
     * @return void
     */
    #[Test]
    function user_cannot_add_anime_to_their_library_with_an_invalid_status(): void
    {
        // Send request to add first anime to library
        $model = Anime::first();

        $response = $this->addModelToLibraryAPIRequest($model, null);

        // Check whether the response was unsuccessful
        $this->assertNotNull($response['errors']);
    }

    /**
     * User can delete anime from their library.
     *
     * @return void
     */
    #[Test]
    function user_can_delete_anime_from_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::InProgress());

        // Send the request
        $response = $this->auth()->postJson(route('api.me.library.delete', [
            'library' => UserLibraryKind::Anime,
            'model_id' => $this->anime->getKey(),
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user has no longer tracked the model
        $this->assertTrue($this->user->hasNotTracked($this->anime));
    }

    /**
     * User can search in own library.
     *
     * @return void
     */
    #[Test]
    function user_can_search_in_own_library(): void
    {
        // Add an anime to the user's list
        $models = Anime::factory(20)->create();

        /** @var Anime $model */
        foreach ($models as $model) {
            $this->user->track($model, UserLibraryStatus::InProgress());
        }

        // Send the request
        $response = $this->auth()->getJson(route('api.search.index', [
            'scope' => SearchScope::Library,
            'types' => [SearchType::Shows],
            'query' => $models->random()->original_title,
        ]));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the result count is greater than zero
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    /**
     * Sends an API request to add anime to a user's library.
     *
     * @param Model                  $model
     * @param UserLibraryStatus|null $status
     *
     * @return TestResponse
     */
    private function addModelToLibraryAPIRequest(Model $model, ?UserLibraryStatus $status): TestResponse
    {
        return $this->auth()->postJson(route('api.me.library.index', [
            'library' => UserLibraryKind::Anime,
            'model_id' => $model->getKey(),
            'status' => $status?->key ?? 'Invalid status'
        ]));
    }
}
