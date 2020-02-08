<?php

namespace Tests;

use App\Session;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use KuroAuthToken;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\API\Traits\ProvidesTestUser;
use Tests\API\Traits\RunsSeeders;

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
            $this->assertSuccessful()
                ->assertJson([
                    'success' => true
                ]);
        });

        TestResponse::macro('assertUnsuccessfulAPIResponse', function() {
            $this->assertStatus(400)
                ->assertJson([
                    'success' => false
                ]);
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
    protected function auth() {
        if(!isset($this->user))
            $this->fail('Used "authHeader", but no user present.');

        // Create a session
        $session = factory(Session::class)->create([
            'user_id'           => $this->user->id,
            'device'            => 'PHPUnit Test Suite',
            'ip'                => '127.0.0.1'
        ]);

        // Attach the auth header
        $this->withHeader('kuro-auth', KuroAuthToken::generate($this->user->id, $session->secret));

        return $this;
    }
}
