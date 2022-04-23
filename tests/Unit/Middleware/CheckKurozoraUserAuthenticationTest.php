<?php

namespace Tests\Unit\Middleware;

use App\Helpers\JSONResult;
use Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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
    function routes_that_require_authentication_cannot_be_accessed_without_being_authenticated(): void
    {
        $response = $this->get('/auth-required');

        $this->assertEquals(403, $response->json('errors.0.status'));
    }

    /** @test */
    function routes_that_require_authentication_can_be_accessed_when_authenticated(): void
    {
        $response = $this->auth()->get('/auth-required');
        $json = $response->json();

        $response->assertSuccessful();
        $this->assertTrue($json['is_authenticated']);
        $this->assertSame($this->user->id, $json['authenticated_user_id']);
    }

    /** @test */
    function routes_with_optional_authentication_can_be_accessed_without_being_authenticated(): void
    {
        $response = $this->get('/auth-optional');
        $json = $response->json();

        $response->assertSuccessful();
        $this->assertSame(false, $json['is_authenticated']);
    }

    /** @test */
    function routes_with_optional_authentication_can_be_accessed_when_authenticated(): void
    {
        $response = $this->auth()->get('/auth-optional');
        $json = $response->json();

        $response->assertSuccessful();
        $this->assertTrue($json['is_authenticated']);
        $this->assertSame($this->user->id, $json['authenticated_user_id']);
    }

    /**
     * Registers endpoints to test the middleware.
     *
     * @return void
     */
    private function registerTestRoutes(): void
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
        })->middleware('auth.kurozora');

        Route::get('/auth-optional', function() use ($userInfoResponse) {
            return $userInfoResponse();
        })->middleware('auth.kurozora:optional');
    }
}
