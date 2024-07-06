<?php

namespace Tests\API;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

class ReminderTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestAnime;

    /**
     * Test if a normal user cannot add anime to their reminders.
     *
     * @return void
     */
    #[Test]
    function a_normal_user_cannot_add_anime_to_their_reminders(): void
    {
        // Send request to add anime to the user's reminders
        $response = $this->auth()->postJson(route('api.me.reminders.create'), [
            'library' => UserLibraryKind::Anime,
            'model_id' => (string) $this->anime->id,
        ]);

        // Check whether the request was successful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the user now has 1 anime in their reminders
        $this->assertFalse($this->user->hasReminded($this->anime));
    }

    /**
     * Test if a subscribed user can add anime to their reminders.
     *
     * @return void
     */
    #[Test]
    function a_subscribed_user_can_add_anime_to_their_reminders(): void
    {
        // Make user a subscriber
        $this->user->update([
            'is_subscribed' => true
        ]);

        // Send request to add the anime to the user's reminders
        $response = $this->auth()->postJson(route('api.me.reminders.create'), [
            'library' => UserLibraryKind::Anime,
            'model_id' => (string) $this->anime->id,
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user now has 1 anime in their reminders
        $this->assertTrue($this->user->hasReminded($this->anime));
    }

    /**
     * Test if a subscribed_user can remove anime from their reminders.
     *
     * @return void
     */
    #[Test]
    function a_subscribed_user_can_remove_anime_from_their_reminders(): void
    {
        // Add the anime to the user's reminders.
        $this->user->remind($this->anime);

        // Make user a subscriber
        $this->user->update([
            'is_subscribed' => true
        ]);

        // Send request to remove the anime from the user's reminders
        $response = $this->auth()->postJson(route('api.me.reminders.create', [
            'library' => UserLibraryKind::Anime,
            'model_id' => (string) $this->anime->getKey(),
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user now has no anime in their reminders
        $this->assertFalse($this->user->hasReminded($this->anime));
    }

    /**
     * Test if a user can get a list of the anime in their reminders.
     *
     * @return void
     */
    #[Test]
    function a_user_can_get_a_list_of_the_anime_in_their_reminders(): void
    {
        // Add some anime to the user's reminders
        /** @var Anime[] $animeList */
        $animeList = Anime::factory(25)->create();

        foreach($animeList as $anime) {
            $this->user->remind($anime);
        }

        // Send request for the list of anime
        $response = $this->auth()->getJson(route('api.me.reminders.index'));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertCount(25, $response->json()['data']);
    }
}
