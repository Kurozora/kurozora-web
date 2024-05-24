<?php

namespace Tests\API;

use App\Enums\MediaCollection;
use App\Models\SessionAttribute;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
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
     */
    #[Test]
    public function user_can_get_own_details_with_authentication_token(): void
    {
        // Send request
        $response = $this->auth()->json('GET', 'v1/me');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user id in the response is the current user's id
        $this->assertEquals($this->user->id, $response->json()['data'][0]['id']);
    }

    /**
     * User cannot get own details without authentication token.
     *
     * @return void
     */
    #[Test]
    public function user_cannot_get_own_details_without_authentication_token(): void
    {
        // Send request
        $response = $this->json('GET', 'v1/me', []);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }


    /**
     * User can update their biography.
     *
     * @return void
     */
    #[Test]
    public function user_can_update_their_biography(): void
    {
        // Remove the user's biography
        $this->user->update(['biography' => null]);

        // Send the update request
        $response = $this->auth()->json('POST', 'v1/me', [
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
     */
    #[Test]
    public function user_can_remove_their_biography(): void
    {
        // Set the user's biography
        $this->user->update(['biography' => 'I love Kurozora!']);

        // Send the update request
        $response = $this->auth()->json('POST', 'v1/me', [
            'biography' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the biography is empty
        $this->user->refresh();
        $this->assertEmpty($this->user->biography);
    }

    /**
     * User can update their profile image.
     *
     * @return void
     */
    #[Test]
    public function user_can_update_their_profile_image(): void
    {
        // Create fake storage
        Storage::fake('profile_images');

        // Create fake 100kb image
        $uploadFile = UploadedFile::fake()->image('ProfileImage.jpg', 250, 250)->size(100);

        // Send the update request
        $response = $this->auth()->json('POST', 'v1/me', [
            'profileImage' => $uploadFile
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the profile image was uploaded properly
        $profileImage = $this->user->getFirstMedia(MediaCollection::Profile);

        $this->assertNotNull($profileImage);
        $this->assertFileExists($profileImage->first()->getPath());

        // Delete the profile image
        $this->user->clearMediaCollection(MediaCollection::Profile);
    }

    /**
     * User can remove their profile image.
     *
     * @return void
     * @return void
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     */
    #[Test]
    function user_can_remove_their_profile_image(): void
    {
        // Create fake storage
        Storage::fake('profile_images');

        // Create fake 100kb image and set it as the profile image
        $uploadFile = UploadedFile::fake()->image('ProfileImage.jpg', 250, 250)->size(100);

        $this->user->updateImageMedia(MediaCollection::Profile(), $uploadFile);

        // Send the update request
        $response = $this->auth()->json('POST', 'v1/me', [
            'profileImage' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the profile image was removed properly
        $profileImage = $this->user->getFirstMedia(MediaCollection::Profile);

        $this->assertNull($profileImage);
    }

    /**
     * User can update their banner.
     *
     * @return void
     */
    #[Test]
    public function user_can_update_their_banner(): void
    {
        // Create fake storage
        Storage::fake('banners');

        // Create fake 100kb image
        $uploadFile = UploadedFile::fake()->image('banner.jpg', 250, 250)->size(100);

        // Send the update request
        $response = $this->auth()->json('POST', 'v1/me', [
            'bannerImage' => $uploadFile
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the banner image was uploaded properly
        $bannerImageImage = $this->user->getFirstMedia(MediaCollection::Banner);

        $this->assertNotNull($bannerImageImage);
        $this->assertFileExists($bannerImageImage->first()->getPath());

        // Delete the banner
        $this->user->clearMediaCollection(MediaCollection::Banner);
    }

    /**
     * User can remove their banner.
     *
     * @return void
     * @return void
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     */
    #[Test]
    function user_can_remove_their_banner(): void
    {
        // Create fake storage
        Storage::fake('banners');

        // Create fake 100kb image and set it as the banner
        $uploadFile = UploadedFile::fake()->image('banner.jpg', 250, 250)->size(100);

        $this->user->updateImageMedia(MediaCollection::Banner(), $uploadFile);

        // Send the update request
        $response = $this->auth()->json('POST', 'v1/me', [
            'bannerImage' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the banner was removed properly
        $bannerImage = $this->user->getFirstMedia(MediaCollection::Banner);

        $this->assertNull($bannerImage);
    }

    /**
     * User can get their list of followers.
     *
     * @return void
     */
    #[Test]
    public function user_can_get_their_followers_list(): void
    {
        // Add a follower
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->user->followers()->attach($anotherUser);

        // Request the list of followers
        $response = $this->auth()->json('GET', 'v1/me/followers');

        // Check that the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check that the response contains the follower
        $this->assertNotEmpty($response['data']);
    }

    /**
     * User can get their list of following.
     *
     * @return void
     */
    #[Test]
    public function user_can_get_their_following_list(): void
    {
        // Add a user to the following list
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->user->following()->attach($anotherUser);

        // Request the list of following
        $response = $this->auth()->json('GET', 'v1/me/following');

        // Check that the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check that the response contains the user
        $this->assertNotEmpty($response['data']);
    }

    /**
     * User can get a list of their auth token related session attributes.
     *
     * @return void
     */
    #[Test]
    public function user_can_get_a_list_of_their_auth_token_related_session_attributes(): void
    {
        // Create some sessions for the user
        $personalAccessTokens = [];
        foreach (range(1, 25) as $index) {
            $personalAccessTokens[] = $this->user->createToken('user_can_get_a_list_of_their_auth_token_related_session_attributes ' . $index);
        }

        foreach ($personalAccessTokens as $personalAccessToken) {
            SessionAttribute::factory()->create([
                'model_id' => $personalAccessToken->accessToken->token
            ]);
        }

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/access-tokens');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the sessions
        $this->assertCount(25, $response->json()['data']);
    }
}
