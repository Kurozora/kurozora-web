<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Settings;
use Auth;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class SettingsTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Can create a new instance by providing a user.
     *
     * @test
     */
    function can_create_a_new_instance_by_providing_a_user(): void
    {
        // Create a new settings instance from the given user.
        $settings = Settings::create($this->user);

        // Assert the settings object is not null.
        $this->assertNotNull($settings);
    }

    /**
     * Can get all settings.
     *
     * @test
     */
    function can_get_all_settings(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert settings array is not empty.
        $this->assertNotEmpty(settings()->all());
    }

    /**
     * Can get a specific setting.
     *
     * @test
     */
    function can_get_a_specific_setting(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert value type is bool when using the get method.
        $this->assertIsBool(settings()->get('can_change_username'));

        // Assert the value type is bool when accessing via magic.
        $this->assertIsBool(settings()->can_change_username);
    }

    /**
     * Cannot get a setting that does not exist.
     *
     * @test
     */
    function cannot_get_a_setting_that_does_not_exist(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert that an instance of Exception was thrown.
        $this->expectException(Exception::class);

        // Throw Exception.
        $randomKey = settings()->random_key;
    }

    /**
     * Can set a setting that already exists.
     *
     * @test
     */
    function can_set_a_setting_that_already_exist(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Asser the can_change_username setting is false.
        $this->assertFalse(settings()->get('can_change_username'));

        // Change the can_change_username setting to true.
        $isSet = settings()->set('can_change_username', true);

        // Assert the set was successful.
        $this->assertTrue($isSet);

        // Assert the can_change_username setting is now true.
        $this->assertTrue(settings()->get('can_change_username'));
    }

    /**
     * Can set a new setting that does not exist.
     *
     * @test
     */
    function can_set_a_new_setting_that_does_not_exist(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert the new_setting_exists setting is null.
        $this->assertNull(settings()->get('new_setting_exists'));

        // Set the new_setting_exists setting with value true.
        $isSet = settings()->set('new_setting_exists', true);

        // Assert the set was successful.
        $this->assertTrue($isSet);

        // Asser the new_setting_exists setting now exists with the value true.
        $this->assertTrue(settings()->get('new_setting_exists'));
    }

    /**
     * Can determine whether a setting exists.
     *
     * @test
     */
    function can_determine_whether_a_setting_exists(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert the settings contains a can_change_username setting.
        $this->assertTrue(settings()->has('can_change_username'));
    }

    /**
     * Can determine whether a setting does not exists.
     *
     * @test
     */
    function can_determine_whether_a_setting_does_not_exists(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert the settings does not contain a new_setting_exists setting.
        $this->assertFalse(settings()->has('new_setting_exists'));
    }

    /**
     * Can merge the provided attributes with the settings without adding new settings.
     *
     * @test
     */
    function can_merge_the_provided_attributes_with_the_settings_without_adding_new_settings(): void
    {
        // The new attributes
        $attributes = [
            'can_change_username'   => true,
            'tv_rating'             => 1,
            'new_setting_exists'    => true,
        ];

        // Authenticate the user.
        Auth::login($this->user);

        // Assert the existing settings are how we expect them.
        $this->assertFalse(settings('can_change_username'));
        $this->assertFalse(settings('tv_rating') == 1);

        // Merge the settings.
        $isMerged = settings()->merge($attributes);

        // Assert the merge was successful.
        $this->assertTrue($isMerged);

        // Assert the existing settings were updated.
        $this->assertTrue(settings('can_change_username'));
        $this->assertTrue(settings('tv_rating') == 1);

        // Assert the new setting was not added.
        $this->assertNull(settings('new_settings_exists'));
    }

    /**
     * Settings method returns the settings object if nothing is provided.
     *
     * @test
     */
    function settings_method_returns_the_settings_object_if_nothing_is_provided(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert a Settings instance is returned.
        $this->assertInstanceOf(Settings::class, settings());
    }

    /**
     * Settings method can get a setting if key is provided.
     *
     * @test
     */
    function settings_method_can_get_a_setting_if_key_is_provided(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert the can_change_username setting is false.
        $this->assertFalse(settings('can_change_username'));
    }

    /**
     * Settings method cannot get a setting if the provided key does not exist.
     *
     * @test
     */
    function settings_method_cannot_get_a_setting_if_the_provided_key_does_not_exist(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert the new_setting_exists setting is null.
        $this->assertNull(settings('new_setting_exists'));
    }

    /**
     * Settings method can set an existing setting if a key and a value is provided.
     *
     * @test
     */
    function settings_method_can_set_an_existing_setting_if_a_key_and_a_value_is_provided(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Assert can_change_username is false before setting.
        $this->assertFalse(settings()->get('can_change_username'));

        // Add a new setting.
        $isSet = settings('can_change_username', true);

        // Assert the set is successful.
        $this->assertTrue($isSet);

        // Assert the new setting exists.
        $this->assertTrue(settings('can_change_username'));
    }

    /**
     * Settings method can set a new setting if a key and a value is provided.
     *
     * @test
     */
    function settings_method_can_set_a_new_setting_if_a_key_and_a_value_is_provided(): void
    {
        // Authenticate the user.
        Auth::login($this->user);

        // Add a new setting.
        $isSet = settings('new_setting_exists', true);

        // Assert the set is successful.
        $this->assertTrue($isSet);

        // Assert the new setting exists.
        $this->assertNotNull(settings()->get('new_setting_exists'));
    }
}
