<?php

namespace Tests;

use App\Models\SessionAttribute;
use App\Models\User;
use Illuminate\Foundation\Mix;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Spatie\Snapshots\MatchesSnapshots;

abstract class TestCase extends BaseTestCase
{
    use MatchesSnapshots;

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
        TestResponse::macro('assertSuccessfulAPIResponse', function () {
            $this->assertSuccessful();
        });

        TestResponse::macro('assertUnsuccessfulAPIResponse', function () {
            PHPUnit::assertFalse(
                $this->isSuccessful(),
                'Response status code [' . $this->getStatusCode() . '] is not an unsuccessful status code.'
            );
        });

        // Tells the API to always return JSON results
        $this->withHeader('Accept', 'application/json');
    }

    /**
     * API auth header
     *
     * This function will create a session for the user, and attach the auth token to the request.
     *
     * @return $this
     */
    protected function auth(): TestCase
    {
        /** @var User $user */
        $user = $this->user;

        if (!isset($user)) {
            $this->fail('Used "auth", but no user present.');
        }

        // Add bearer token to header
        $personalAccessToken = $user->createToken('Auth token');
        $this->withHeader('Authorization', 'Bearer ' . $personalAccessToken->plainTextToken);
        SessionAttribute::factory()->create([
            'model_id' => $personalAccessToken->accessToken->token
        ]);

        // Authenticate user
        Sanctum::actingAs($user, ['*']);

        return $this;
    }
}
