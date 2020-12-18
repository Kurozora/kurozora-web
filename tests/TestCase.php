<?php

namespace Tests;

use App\Helpers\KuroAuthToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;
use Tests\Traits\RunsSeeders;

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
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[ProvidesTestUser::class])) {
            $this->initializeTestUser();
        }
        if (isset($uses[ProvidesTestAnime::class])) {
            $this->initializeTestAnime();
        }
        if (isset($uses[RunsSeeders::class])) {
            $this->seedDatabase();
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
    protected function auth()
    {
        if(!isset($this->user))
            $this->fail('Used "authHeader", but no user present.');

        // Create a session
        $session = $this->user->createSession();

        // Attach the auth header
        $this->withHeader('kuro-auth', KuroAuthToken::generate($this->user->id, $session->secret));

        return $this;
    }
}
