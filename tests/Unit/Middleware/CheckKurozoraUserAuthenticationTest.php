<?php

namespace Tests\Unit\Middleware;

use App\Helpers\JSONResult;
use App\Helpers\KuroAuthToken;
use App\Http\Middleware\CheckKurozoraUserAuthentication;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class CheckKurozoraUserAuthenticationTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->registerTestRoutes();
    }

    /** @test */
    function routes_that_require_authentication_cannot_be_accessed_without_being_authenticated()
    {
        $response = $this->get('/auth-required');

        $this->assertEquals($response->json('errors.0.status'), 403);
    }

    /** @test */
    function routes_that_require_authentication_can_be_accessed_when_authenticated()
    {
        $response = $this->auth()->get('/auth-required');
        $json = $response->json();

        $response->assertSuccessful();
        $this->assertSame(true, $json['is_authenticated']);
        $this->assertSame($this->user->id, $json['authenticated_user_id']);
    }

    /** @test */
    function routes_with_optional_authentication_can_be_accessed_without_being_authenticated()
    {
        $response = $this->get('/auth-optional');
        $json = $response->json();

        $response->assertSuccessful();
        $this->assertSame(false, $json['is_authenticated']);
    }

    /** @test */
    function routes_with_optional_authentication_can_be_accessed_when_authenticated()
    {
        $response = $this->auth()->get('/auth-optional');
        $json = $response->json();

        $response->assertSuccessful();
        $this->assertSame(true, $json['is_authenticated']);
        $this->assertSame($this->user->id, $json['authenticated_user_id']);
    }

    /** @test */
    function authentication_is_not_accepted_when_the_session_is_expired()
    {
        // Create a session and expire it
        $session = $this->user->createSession();
        $session->expires_at = now();
        $session->save();

        // Attach the auth header
        $this->withHeader('kuro-auth', KuroAuthToken::generate($this->user->id, $session->secret));

        // Perform request
        $response = $this->get('/auth-required');

        $response->assertStatus(403);
    }

    /** @test */
    function session_lifetime_is_extended_when_passing_through_the_middleware()
    {
        Carbon::setTestNow();

        // Create a session and subtract a day from its expiry
        $session = $this->user->createSession();
        $session->expires_at = now()->addDays(Session::VALID_FOR_DAYS)->subDay();
        $session->save();

        // Attach the auth header
        $this->withHeader('kuro-auth', KuroAuthToken::generate($this->user->id, $session->secret));

        // Perform request
        $this->get('/auth-required');

        // Check whether the expiry was extended
        $session->refresh();

        $this->assertEquals($session->expires_at->startOfDay(), now()->addDays(Session::VALID_FOR_DAYS)->startOfDay());
    }

    /**
     * Registers endpoints to test the middleware.
     *
     * @return void
     */
    private function registerTestRoutes()
    {
        $userInfoResponse = function()
        {
            return JSONResult::success([
                'is_authenticated'      => Auth::check(),
                'authenticated_user_id' => Auth::check() ? Auth::id() : null
            ]);
        };

        Route::get('/auth-required', function() use ($userInfoResponse) {
            return $userInfoResponse();
        })->middleware(CheckKurozoraUserAuthentication::class);

        Route::get('/auth-optional', function() use ($userInfoResponse) {
            return $userInfoResponse();
        })->middleware(CheckKurozoraUserAuthentication::class . ':optional');
    }
}
