<?php

namespace Tests\API;

use App\Anime;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\ProvidesTestUser;
use Tests\TestCase;

class ReminderAnimeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can add anime to their reminders.
     *
     * @return void
     * @test
     */
    function a_user_can_add_anime_to_their_reminders()
    {
        // Send request to add anime to the user's reminders
        /** @var Anime $anime */
        $anime = factory(Anime::class)->create();

        $this->user->library()->attach($anime);

        $response = $this->auth()->json('POST', '/api/v1/me/reminder-anime', [
            'anime_id'      => $anime->id,
            'is_reminded'   => 1
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user now has 1 anime in their reminders
        $this->assertEquals(1, $this->user->reminderAnime()->count());
    }

    /**
     * Test if a user can remove anime from their reminders.
     *
     * @return void
     * @test
     */
    function a_user_can_remove_anime_from_their_reminders()
    {
        // Add the anime to the user's reminders
        /** @var Anime $anime */
        $anime = factory(Anime::class)->create();

        $this->user->library()->attach($anime);
        $this->user->reminderAnime()->attach($anime->id);

        // Send request to remove the anime from the user's reminders
        $response = $this->auth()->json('POST', '/api/v1/me/reminder-anime', [
            'anime_id'      => $anime->id,
            'is_reminded'   => 0
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user now has no anime in their reminders
        $this->assertEquals(0, $this->user->reminderAnime()->count());
    }

    /**
     * Test if a user can get a list of the anime in their reminders.
     *
     * @return void
     * @test
     */
    function a_user_can_get_a_list_of_the_anime_in_their_reminders()
    {
        // Add some anime to the user's reminders
        /** @var Anime[] $anime */
        $animeList = factory(Anime::class, 30)->create();

        foreach($animeList as $anime)
            $this->user->reminderAnime()->attach($anime->id);

        // Send request for the list of anime
        $response = $this->auth()->json('GET', '/api/v1/me/reminder-anime');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertCount(30, $response->json()['data']);
    }
}
