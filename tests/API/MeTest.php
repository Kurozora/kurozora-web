<?php

namespace Tests\API;

use App\Models\Anime;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class MeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * User can get own details with authentication token.
     *
     * @return void
     * @test
     */
    public function user_can_get_own_details_with_authentication_token()
    {
        // Send request
        $response = $this->auth()->json('GET', '/api/v1/me');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user id in the response is the current user's id
        $this->assertEquals($this->user->id, $response->json()['data'][0]['id']);
    }

    /**
     * User cannot get own details without authentication token.
     *
     * @return void
     * @test
     */
    public function user_cannot_get_own_details_without_authentication_token()
    {
        // Send request
        $response = $this->json('GET', '/api/v1/me', []);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }


    /**
     * User can update their biography.
     *
     * @return void
     * @test
     */
    function user_can_update_their_biography()
    {
        // Remove the user's biography
        $this->user->update(['biography' => null]);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/me', [
            'biography' => 'I love Kurozora!'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the biography matches the new string
        $this->user->refresh();
        $this->assertEquals('I love Kurozora!', $this->user->biography);
    }

    /**
     * User can remove their biography.
     *
     * @return void
     * @test
     */
    function user_can_remove_their_biography()
    {
        // Set the user's biography
        $this->user->update(['biography' => 'I love Kurozora!']);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/me', [
            'biography' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the biography was removed
        $this->user->refresh();
        $this->assertNull($this->user->biography);
    }

    /**
     * User can update their avatar.
     *
     * @return void
     * @test
     */
    function user_can_update_their_avatar()
    {
        // Create fake storage
        Storage::fake('avatars');

        // Create fake 100kb image
        $image = UploadedFile::fake()->image('avatar.jpg', 250, 250)->size(100);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/me', [
            'profileImage' => $image
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the avatar was uploaded properly
        $avatar = $this->user->getMedia('avatar');

        $this->assertCount(1, $avatar);
        $this->assertFileExists($avatar->first()->getPath());

        // Delete the avatar
        $this->user->clearMediaCollection('avatar');
    }

    /**
     * User can remove their avatar.
     *
     * @return void
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @test
     */
    function user_can_remove_their_avatar()
    {
        // Create fake storage
        Storage::fake('avatars');

        // Create fake 100kb image and set it as the avatar
        $image = UploadedFile::fake()->image('avatar.jpg', 250, 250)->size(100);

        $this->user->addMedia($image)->toMediaCollection('avatar');

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/me', [
            'profileImage' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the avatar was removed properly
        $avatar = $this->user->getMedia('avatar');

        $this->assertCount(0, $avatar);
    }

    /**
     * User can update their banner.
     *
     * @return void
     * @test
     */
    function user_can_update_their_banner()
    {
        // Create fake storage
        Storage::fake('banners');

        // Create fake 100kb image
        $image = UploadedFile::fake()->image('banner.jpg', 250, 250)->size(100);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/me', [
            'bannerImage' => $image
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the banner image was uploaded properly
        $banner = $this->user->getMedia('banner');

        $this->assertCount(1, $banner);
        $this->assertFileExists($banner->first()->getPath());

        // Delete the banner
        $this->user->clearMediaCollection('banner');
    }

    /**
     * User can remove their banner.
     *
     * @return void
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @test
     */
    function user_can_remove_their_banner()
    {
        // Create fake storage
        Storage::fake('banners');

        // Create fake 100kb image and set it as the banner
        $image = UploadedFile::fake()->image('banner.jpg', 250, 250)->size(100);

        $this->user->addMedia($image)->toMediaCollection('banner');

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/me', [
            'bannerImage' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the banner was removed properly
        $banner = $this->user->getMedia('banner');

        $this->assertCount(0, $banner);
    }

    /**
     * User can get a list of their favorite anime.
     *
     * @return void
     * @test
     */
    function user_can_get_a_list_of_their_favorite_anime()
    {
        // Add some anime to the user's favorites
        /** @var Anime[] $anime */
        $animeList = Anime::factory(25)->create();

        foreach($animeList as $anime)
            $this->user->favoriteAnime()->attach($anime->id);

        // Send request for the list of anime
        $response = $this->auth()->json('GET', '/api/v1/me/favorite-anime');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertCount(25, $response->json()['data']);
    }


    /**
     * User can get their list of followers.
     *
     * @return void
     * @test
     */
    function user_can_get_their_followers_list()
    {
        // Add a follower
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->user->followers()->attach($anotherUser);

        // Request the list of followers
        $response = $this->auth()->json('GET', '/api/v1/me/followers');

        // Check that the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check that the response contains the follower
        $this->assertTrue($response['data'] > 0);
    }

    /**
     * User can get their list of following.
     *
     * @return void
     * @test
     */
    function user_can_get_their_following_list()
    {
        // Add a user to the following list
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->user->following()->attach($anotherUser);

        // Request the list of following
        $response = $this->auth()->json('GET', '/api/v1/me/following');

        // Check that the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check that the response contains the user
        $this->assertTrue($response['data'] > 0);
    }

    /**
     * User can get a list of their sessions.
     *
     * @return void
     * @test
     */
    function user_can_get_a_list_of_their_sessions()
    {
        // Create some sessions for the user
        Session::factory(25)->create(['user_id' => $this->user->id]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/me/sessions');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the sessions
        $this->assertCount(25, $response->json()['data']);
    }

}
