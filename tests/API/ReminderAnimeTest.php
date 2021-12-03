<?php

namespace Tests\API;

use App\Models\Anime;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class ReminderAnimeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a normal user cannot add anime to their reminders.
     *
     * @return void
     * @test
     */
    function a_normal_user_cannot_add_anime_to_their_reminders()
    {
        $this->markTestIncomplete('Test will work once server sided IAP check is added.');
        // Send request to add anime to the user's reminders
        /** @var Anime $anime */
        $anime = Anime::factory()->create();

        $this->user->library()->attach($anime);

        $response = $this->auth()->json('POST', 'v1/me/reminder-anime', [
            'anime_id'      => $anime->id,
            'is_reminded'   => 1
        ]);

        // Check whether the request was successful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the user now has 1 anime in their reminders
        $this->assertEquals(0, $this->user->reminderAnime()->count());
    }

    /**
     * Test if a pro user can add anime to their reminders.
     *
     * @return void
     * @test
     */
    function a_pro_user_can_add_anime_to_their_reminders()
    {
        // Send request to add anime to the user's reminders
        /** @var Anime $anime */
        $anime = Anime::factory()->create();

        $this->user->library()->attach($anime);

        // Attach a receipt to the user
        $this->user->receipt()->create([
            'original_transaction_id' => '1',
            'web_order_line_item_id' => '1',
            'latest_expires_date' => now()->addDay(),
            'latest_receipt_data' => '1',
            'is_subscribed' => 1,
            'will_auto_renew' => 0,
            'subscription_product_id' => 'SKU'
        ]);

        // Send request to add the anime from the user's reminders
        $response = $this->auth()->json('POST', 'v1/me/reminder-anime', [
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
        $anime = Anime::factory()->create();

        $this->user->library()->attach($anime);
        $this->user->reminderAnime()->attach($anime->id);

        // Attach a receipt to the user
        $this->user->receipt()->create([
            'original_transaction_id' => '1',
            'web_order_line_item_id' => '1',
            'latest_expires_date' => now()->addDay(),
            'latest_receipt_data' => '1',
            'is_subscribed' => 1,
            'will_auto_renew' => 0,
            'subscription_product_id' => 'SKU'
        ]);

        // Send request to remove the anime from the user's reminders
        $response = $this->auth()->json('POST', 'v1/me/reminder-anime', [
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
        /** @var Anime[] $animeList */
        $animeList = Anime::factory(25)->create();

        foreach($animeList as $anime) {
            $this->user->reminderAnime()->attach($anime->id);
        }

        // Send request for the list of anime
        $response = $this->auth()->json('GET', 'v1/me/reminder-anime');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertCount(25, $response->json()['data']);
    }
}
