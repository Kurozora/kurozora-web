<?php

namespace Tests\API;

use App\Enums\AppThemeDownloadKind;
use App\Models\AppTheme;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class AppThemeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * A user can view all app themes.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_all_app_themes(): void
    {
        AppTheme::factory(2)->create();

        $response = $this->json('GET', 'v1/theme-store');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the characters array is not empty
        $this->assertNotEmpty($response->json()['data']);
    }

    /**
     * A user can view specific app theme details.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_app_theme_details(): void
    {
        /** @var AppTheme $theme */
        $theme = AppTheme::factory()->create();

        $response = $this->get('v1/theme-store/' . $theme->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the theme id in the response is the desired theme's id
        $this->assertEquals($theme->id, $response->json()['data'][0]['id']);
    }

    /**
     * A user cannot download an app theme when not subscribed or pro.
     *
     * @return void
     */
    #[Test]
    public function a_user_cannot_download_an_app_theme_when_not_subscribed_or_pro(): void
    {
        /** @var AppTheme $theme */
        $theme = AppTheme::factory()->create();

        $response = $this->auth()->getJson(route('api.theme-store.download', $theme->id));

        // Check whether the request was forbidden
        $response->assertStatus(403);
    }

    /**
     * A user can download an app theme as plist.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_download_an_app_theme_as_plist(): void
    {
        /** @var AppTheme $theme */
        $theme = AppTheme::factory()->create();
        $this->user->update([
            'is_pro' => false,
            'is_subscribed' => true,
        ]);

        $response = $this->auth()->json('GET', route('api.theme-store.download', $theme->id), [
            'type' => AppThemeDownloadKind::Plist
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the theme is
        $response->assertHeader('content-type', AppThemeDownloadKind::Plist()->getContentType());
    }

    /**
     * A user can download an app theme as css.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_download_an_app_theme_as_css(): void
    {
        /** @var AppTheme $theme */
        $theme = AppTheme::factory()->create();
        $this->user->update([
            'is_pro' => true,
            'is_subscribed' => false,
        ]);

        $response = $this->auth()->json('GET', route('api.theme-store.download', $theme->id), [
            'type' => AppThemeDownloadKind::CSS
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the theme is
        $response->assertHeader('content-type', AppThemeDownloadKind::CSS()->getContentType());
    }
}
