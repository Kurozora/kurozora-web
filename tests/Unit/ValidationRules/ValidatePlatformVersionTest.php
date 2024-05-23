<?php

namespace Tests\Unit\ValidationRules;

use App\Rules\ValidatePlatformVersion;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidatePlatformVersionTest extends TestCase
{
    /**
     * The platform version validation rule.
     *
     * @var ValidatePlatformVersion $rule
     */
    private ValidatePlatformVersion $rule;

    /**
     * The array of valid platform versions.
     *
     * @var array|string[]
     */
    private array $validPlatformVersions = [
        '1', '1.2', '1.2.0', '5.0', '902.3', '21.23', '3.455'
    ];

    /**
     * The array of invalid platform versions.
     *
     * @var array|string[]
     */
    private array $invalidPlatformVersions = [
        'v1.0', '2.0-rc', 'best version', 'cool', 'xp', '-12.1', '-1'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidatePlatformVersion();
    }

    /**
     * Valid platform versions pass.
     *
     * @return void
     */
    #[Test]
    public function valid_platform_versions_pass(): void
    {
        foreach($this->validPlatformVersions as $platformVersion) {
            $message = '';

            $this->rule->validate('platform_version', $platformVersion, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertEmpty($message, $platformVersion . ' did not pass.' . $message);
        }
    }

    /**
     * Invalid platform versions dont pass.
     *
     * @return void
     */
    #[Test]
    public function invalid_platform_versions_dont_pass(): void
    {
        foreach($this->invalidPlatformVersions as $platformVersion) {
            $message = '';

            $this->rule->validate('platform_version', $platformVersion, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertNotEmpty($message, $platformVersion . ' did not pass.' . $message);
        }
    }
}
