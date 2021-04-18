<?php

namespace Tests;

use App\Helpers\KuroAuthToken;
use App\Models\User;
use Illuminate\Foundation\Mix;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, MatchesSnapshots;

    /**
     * Set up the test.
     *
     * @return void
     */
    function setUp(): void
    {
        parent::setUp();

        // Swap out the Mix manifest implementation, so we don't need
        // to run the npm commands to generate a manifest file for
        // the assets in order to run tests that return views.
        $this->swap(Mix::class, function () {
            return '';
        });

        // API response macro's
        TestResponse::macro('assertSuccessfulAPIResponse', function() {
            $this->assertSuccessful();
        });

        TestResponse::macro('assertUnsuccessfulAPIResponse', function() {
            PHPUnit::assertFalse(
                $this->isSuccessful(),
                'Response status code ['.$this->getStatusCode().'] is not an unsuccessful status code.'
            );
        });
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits(): array
    {
        $uses = parent::setUpTraits();

        if (isset($uses[ProvidesTestUser::class])) {
            $this->initializeTestUser();
        }
        if (isset($uses[ProvidesTestAnime::class])) {
            $this->initializeTestAnime();
        }

        return $uses;
    }

    /**
     * API auth header
     *
     * This function will create a session for the user, and attach
     * .. the auth token to the request.
     *
     * @return $this
     */
    protected function auth(): TestCase
    {
        /** @var User $user */
        $user = $this->user;

        if (!isset($user)) {
            $this->fail('Used "authHeader", but no user present.');
        }

        // Create a session
        $session = $user->createSession();

        // Attach the auth header
        $this->withHeader('kuro-auth', KuroAuthToken::generate($user->id, $session->secret));

        return $this;
    }
}
