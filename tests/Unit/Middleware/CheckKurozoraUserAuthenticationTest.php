<?php

namespace Tests\Unit\Middleware;

use App\Helpers\JSONResult;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
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

    /**
     * Routes that require authentication cannot be accessed without being authenticated.
     *
     * @return void
     */
    #[Test]
    public function routes_that_require_authentication_cannot_be_accessed_without_being_authenticated(): void
    {
        $response = $this->get('/auth-required');

        $this->assertEquals(403, $response->json('errors.0.status'));
    }

    /**
     * Routes that require authentication can be accessed when authenticated.
     *
     * @return void
     */
    #[Test]
    public function routes_that_require_authentication_can_be_accessed_when_authenticated(): void
    {
        $response = $this->auth()->get('/auth-required');
        $json = $response->json();

        $response->assertSuccessful();
        $this->assertTrue($json['is_authenticated']);
        $this->assertSame($this->user->id, $json['authenticated_user_id']);
    }

    /**
     * Routes with optional authentication can be accessed without being authenticated.
     *
     * @return void
     */
    #[Test]
    public function routes_with_optional_authentication_can_be_accessed_without_being_authenticated(): void
    {
        $response = $this->get('/auth-optional');
        $json = $response->json();

        $response->assertSuccessful();
        $this->assertSame(false, $json['is_authenticated']);
    }

    /**
     * Routes with optional authentication can be accessed when authenticated.
     *
     * @return void
     */
    #[Test]
    public function routes_with_optional_authentication_can_be_accessed_when_authenticated(): void
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
                'is_authenticated'      => auth()->check(),
                'authenticated_user_id' => auth()->check() ? auth()->id() : null
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
