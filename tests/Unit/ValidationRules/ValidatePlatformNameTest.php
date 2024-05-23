<?php

namespace Tests\Unit\ValidationRules;

use App\Rules\ValidatePlatformName;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidatePlatformNameTest extends TestCase
{
    /**
     * The platform name validation rule.
     *
     * @var ValidatePlatformName $rule
     */
    private ValidatePlatformName $rule;

    /**
     * The array of valid platform names.
     *
     * @var array|string[] $validPlatformNames
     */
    private array $validPlatformNames = ValidatePlatformName::VALID_PLATFORMS;

    /**
     * The array of invalid platform names.
     *
     * @var array|string[] $invalidPlatformNames
     */
    private array $invalidPlatformNames = [
        'Water Bottle', 'Tin can telephone', 'Walkie Talkie OS'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidatePlatformName();
    }

    /**
     * Valid platform names pass.
     *
     * @return void
     */
    #[Test]
    public function valid_platform_names_pass(): void
    {
        foreach($this->validPlatformNames as $validPlatformName) {
            $message = '';

            $this->rule->validate('platform', $validPlatformName, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertEmpty($message, "$validPlatformName did not pass, while it should have!");
        }
    }

    /**
     * Invalid platform names dont pass.
     *
     * @return void
     */
    #[Test]
    public function invalid_platform_names_dont_pass(): void
    {
        foreach($this->invalidPlatformNames as $invalidPlatformName) {
            $message = '';

            $this->rule->validate('platform', $invalidPlatformName, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertNotEmpty($message, "$invalidPlatformName passed, while it should not have!");
        }
    }
}
