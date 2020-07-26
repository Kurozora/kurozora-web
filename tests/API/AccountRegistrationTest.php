<?php

namespace Tests\API;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountRegistrationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test if an account can be registered.
     *
     * @return void
     * @test
     */
    function an_account_can_be_registered()
    {
        $this->json('POST', '/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(1, User::count());
    }

    /**
     * Test if an account can be registered with an avatar.
     *
     * @return void
     * @test
     */
    function an_account_can_be_registered_with_an_avatar()
    {
        // Create fake storage
        Storage::fake('avatars');

        // Create fake 100kb image
        $image = UploadedFile::fake()->image('avatar.jpg', 250, 250)->size(100);

        // Attempt to register the user
        $this->json('POST', '/api/v1/users', [
            'username'      => 'KurozoraTester',
            'password'      => 'StrongPassword909@!',
            'email'         => 'tester@kurozora.app',
            'profileImage'  => $image
        ])->assertSuccessfulAPIResponse();

        $user = User::first();

        // Double check that the account was created
        $this->assertEquals(1, User::count());

        // Assert that the avatar was uploaded properly
        $avatar = $user->getMedia('avatar');

        $this->assertCount(1, $avatar);
        $this->assertFileExists($avatar->first()->getPath());

        // Delete the media
        $user->clearMediaCollection('avatar');
    }

    /**
     * Test if an account cannot be registered with a large avatar.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_a_large_avatar()
    {
        // Create fake storage
        Storage::fake('avatars');

        // Create fake 1.2mb image
        $image = UploadedFile::fake()->image('avatar.jpg', 250, 250)->size(1200);

        // Attempt to register the user
        $this->json('POST', '/api/v1/users', [
            'username'      => 'KurozoraTester',
            'password'      => 'StrongPassword909@!',
            'email'         => 'tester@kurozora.app',
            'profileImage'  => $image
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }

    /**
     * Test if an account cannot be registered with a PDF as avatar.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_a_pdf_as_avatar()
    {
        // Create fake storage
        Storage::fake('avatars');

        // Create fake 100kb pdf
        $pdfFile = UploadedFile::fake()->create('document.pdf', 100);

        // Attempt to register the user
        $this->json('POST', '/api/v1/users', [
            'username'      => 'KurozoraTester',
            'password'      => 'StrongPassword909@!',
            'email'         => 'tester@kurozora.app',
            'profileImage'  => $pdfFile
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }

    /**
     * Test if an account cannot be registered with a gif as avatar.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_a_gif_as_avatar()
    {
        // Create fake storage
        Storage::fake('avatars');

        // Create fake 100kb gif
        $image = UploadedFile::fake()->image('avatar.gif', 250, 250)->size(100);

        // Attempt to register the user
        $this->json('POST', '/api/v1/users', [
            'username'      => 'KurozoraTester',
            'password'      => 'StrongPassword909@!',
            'email'         => 'tester@kurozora.app',
            'profileImage'  => $image
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }

    /**
     * Test if an account cannot be registered when the username is already in use.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_a_username_that_is_already_in_use()
    {
        // Create the first account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(1, User::count());

        // Attempt to create the second account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'unique@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that there is just 1 account
        $this->assertEquals(1, User::count());
    }

    /**
     * Test if an account cannot be registered when the email is already in use.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_an_email_that_is_already_in_use()
    {
        // Create the first account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(1, User::count());

        // Attempt to create the second account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'UniqueUsername',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that there is just 1 account
        $this->assertEquals(1, User::count());
    }

    /**
     * Test if an account cannot be registered with a long password.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_a_long_password()
    {
        // Generate a password with size of 256
        $longPassword = Str::random(256);

        // Attempt to register the account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'UniqueUsername',
            'password'  => $longPassword,
            'email'     => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }

    /**
     * Test if an account cannot be registered with a short password.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_a_short_password()
    {
        // Attempt to register the account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'UniqueUsername',
            'password'  => 'hi',
            'email'     => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }
}
