<?php

namespace Tests\API;

use App\Enums\MediaCollection;
use App\Models\User;
use File;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Mockery\Exception\InvalidCountException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountRegistrationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     *
     * @throws InvalidCountException
     */
    protected function tearDown(): void
    {
        File::deleteDirectory(config('filesystems.disks.test.root'));

        parent::tearDown(); // Keep at bottom or 'Target class [config] does not exist'
    }

    /**
     * An account can be signed up.
     *
     * @return void
     */
    #[Test]
    function an_account_can_be_signed_up(): void
    {
        $this->json('POST', 'v1/users', [
            'username' => 'Kurozora_Tester-1',
            'password' => 'StrongPassword909@!',
            'email' => 'tester1@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        $this->json('POST', 'v1/users', [
            'username' => 'Kurozora_Tester-2',
            'password' => 'StrongPassword909@!',
            'email' => 'tester2@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        $this->json('POST', 'v1/users', [
            'username' => 'Kurozora_Tester-3',
            'password' => 'StrongPassword909@!',
            'email' => 'tester3@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        $this->json('POST', 'v1/users', [
            'username' => 'Kurozora_Tester-4',
            'password' => 'StrongPassword909@!',
            'email' => 'tester4@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(4, User::count());
    }

    /**
     * An account can be signed up with a profile image.
     *
     * @return void
     */
    #[Test]
    function an_account_can_be_signed_up_with_a_profile_image(): void
    {
        // Create fake 100kb image
        $uploadFile = UploadedFile::fake()->image('ProfileImage.jpg', 250, 250)->size(100);

        // Attempt to sign up the user
        $response = $this->json('POST', 'v1/users', [
            'username' => 'KurozoraTester',
            'password' => 'StrongPassword909@!',
            'email' => 'tester@kurozora.app',
            'profileImage' => $uploadFile
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        $user = User::first();

        // Double check that the account was created
        $this->assertEquals(1, User::count());

        // Assert that the profile image was uploaded properly
        $profileImage = $user->getFirstMedia(MediaCollection::Profile);

        $this->assertNotNull($profileImage);
        $this->assertFileExists($profileImage->first()->getPath());

        // Delete the media
        $user->clearMediaCollection(MediaCollection::Profile);
    }

    /**
     * An account cannot be signed up with a large profile image.
     *
     * @return void
     */
    #[Test]
    function an_account_cannot_be_signed_up_with_a_large_profile_image(): void
    {
        // Create fake 1.2mb image
        $uploadFile = UploadedFile::fake()->image('ProfileImage.jpg', 250, 250)->size(2400);

        // Attempt to signup the user
        $this->json('POST', 'v1/users', [
            'username' => 'KurozoraTester',
            'password' => 'StrongPassword909@!',
            'email' => 'tester@kurozora.app',
            'profileImage' => $uploadFile
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }

    /**
     * An account cannot be signed up with a PDF as profile image.
     *
     * @return void
     */
    #[Test]
    function an_account_cannot_be_singed_up_with_a_pdf_as_profile_image(): void
    {
        // Create fake 100kb pdf
        $uploadFile = UploadedFile::fake()->create('document.pdf', 100);

        // Attempt to signup the user
        $this->json('POST', 'v1/users', [
            'username' => 'KurozoraTester',
            'password' => 'StrongPassword909@!',
            'email' => 'tester@kurozora.app',
            'profileImage' => $uploadFile
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }

    /**
     * An account cannot be signed up with a gif as profile image.
     *
     * @return void
     */
    #[Test]
    function an_account_cannot_be_signed_up_with_a_gif_as_profile_image(): void
    {
        // Create fake 100kb gif
        $uploadFile = UploadedFile::fake()->image('ProfileImage.gif', 250, 250)->size(100);

        // Attempt to sign up the user
        $this->json('POST', 'v1/users', [
            'username' => 'KurozoraTester',
            'password' => 'StrongPassword909@!',
            'email' => 'tester@kurozora.app',
            'profileImage' => $uploadFile
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }

    /**
     * An account cannot be signed up when the username is already in use.
     *
     * @return void
     */
    #[Test]
    function an_account_cannot_be_signed_up_with_a_username_that_is_already_in_use(): void
    {
        // Create the first account
        $this->json('POST', 'v1/users', [
            'nickname' => 'Kurozora_Tester-1',
            'password' => 'StrongPassword909@!',
            'email' => 'tester@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(1, User::count());

        // Attempt to create the second account
        $this->json('POST', 'v1/users', [
            'nickname' => 'Kurozora_Tester-1',
            'password' => 'StrongPassword909@!',
            'email' => 'unique@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that there is just 1 account
        $this->assertEquals(1, User::count());
    }

    /**
     * An account cannot be signed up when the email is already in use.
     *
     * @return void
     */
    #[Test]
    function an_account_cannot_be_signed_up_with_an_email_that_is_already_in_use(): void
    {
        // Create the first account
        $this->json('POST', 'v1/users', [
            'username' => 'KurozoraTester',
            'password' => 'StrongPassword909@!',
            'email' => 'tester@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(1, User::count());

        // Attempt to create the second account
        $this->json('POST', 'v1/users', [
            'username' => 'UniqueUsername',
            'password' => 'StrongPassword909@!',
            'email' => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that there is just 1 account
        $this->assertEquals(1, User::count());
    }

    /**
     * An account cannot be signed up with a long password.
     *
     * @return void
     */
    #[Test]
    function an_account_cannot_be_signed_up_with_a_long_password(): void
    {
        // Generate a password with size of 256
        $longPassword = Str::random(256);

        // Attempt to signup the account
        $this->json('POST', 'v1/users', [
            'username' => 'UniqueUsername',
            'password' => $longPassword,
            'email' => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }

    /**
     * An account cannot be signed up with a short password.
     *
     * @return void
     */
    #[Test]
    function an_account_cannot_be_signed_up_with_a_short_password(): void
    {
        // Attempt to signup the account
        $this->json('POST', 'v1/users', [
            'username' => 'UniqueUsername',
            'password' => 'hi',
            'email' => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(0, User::count());
    }
}
