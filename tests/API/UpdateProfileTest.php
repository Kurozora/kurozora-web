<?php

namespace Tests\API;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\API\Traits\ProvidesTestUser;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can update their biography.
     *
     * @return void
     * @test
     */
    function a_user_can_update_their_biography()
    {
        // Remove the user's biography
        $this->user->update(['biography' => null]);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/profile', [
            'biography' => 'I love Kurozora!'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the biography matches the new string
        $this->user->refresh();
        $this->assertEquals('I love Kurozora!', $this->user->biography);
    }

    /**
     * Test if a user can remove their biography.
     *
     * @return void
     * @test
     */
    function a_user_can_remove_their_biography()
    {
        // Set the user's biography
        $this->user->update(['biography' => 'I love Kurozora!']);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/profile', [
            'biography' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the biography was removed
        $this->user->refresh();
        $this->assertNull($this->user->biography);
    }

    /**
     * Test if a user can update their avatar.
     *
     * @return void
     * @test
     */
    function a_user_can_update_their_avatar()
    {
        // Create fake storage
        Storage::fake('avatars');

        // Create fake 100kb image
        $image = UploadedFile::fake()->image('avatar.jpg', 250, 250)->size(100);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/profile', [
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
     * Test if a user can remove their avatar.
     *
     * @return void
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     * @test
     */
    function a_user_can_remove_their_avatar()
    {
        // Create fake storage
        Storage::fake('avatars');

        // Create fake 100kb image and set it as the avatar
        $image = UploadedFile::fake()->image('avatar.jpg', 250, 250)->size(100);

        $this->user->addMedia($image)->toMediaCollection('avatar');

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/profile', [
            'profileImage' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the avatar was removed properly
        $avatar = $this->user->getMedia('avatar');

        $this->assertCount(0, $avatar);
    }

    /**
     * Test if a user can update their banner.
     *
     * @return void
     * @test
     */
    function a_user_can_update_their_banner()
    {
        // Create fake storage
        Storage::fake('banners');

        // Create fake 100kb image
        $image = UploadedFile::fake()->image('banner.jpg', 250, 250)->size(100);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/profile', [
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
     * Test if a user can remove their banner.
     *
     * @return void
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     * @test
     */
    function a_user_can_remove_their_banner()
    {
        // Create fake storage
        Storage::fake('banners');

        // Create fake 100kb image and set it as the banner
        $image = UploadedFile::fake()->image('banner.jpg', 250, 250)->size(100);

        $this->user->addMedia($image)->toMediaCollection('banner');

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/profile', [
            'bannerImage' => null
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that the banner was removed properly
        $banner = $this->user->getMedia('banner');

        $this->assertCount(0, $banner);
    }

    /**
     * Test if a user cannot update another user's profile.
     *
     * @return void
     * @test
     */
    function a_user_cannot_update_another_users_profile()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        // Set the user's biography
        $anotherUser->update(['biography' => 'I love Kurozora!']);

        // Send the update request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $anotherUser->id . '/profile', [
            'biography' => null
        ]);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the biography was removed
        $anotherUser->refresh();
        $this->assertEquals('I love Kurozora!', $anotherUser->biography);
    }
}
