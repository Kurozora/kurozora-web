<?php

namespace Tests\API;

use App\Models\AppTheme;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AppThemeTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A user can view all app themes.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_all_app_themes()
    {
        AppTheme::factory(2)->create();

        $response = $this->json('GET', 'v1/theme-store', []);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the characters array is not empty
        $this->assertNotEmpty($response->json()['data']);
    }

    /**
     * A user can view specific app theme details.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_specific_app_theme_details()
    {
        /** @var AppTheme $theme */
        $theme = AppTheme::factory()->create();

        $response = $this->get('v1/theme-store/'.$theme->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the theme id in the response is the desired theme's id
        $this->assertEquals($theme->id, $response->json()['data'][0]['id']);
    }
}
