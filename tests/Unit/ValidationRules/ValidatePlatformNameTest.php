<?php

namespace Tests\Unit\ValidationRules;

use App\Rules\ValidatePlatformName;
use Tests\TestCase;

class ValidatePlatformNameTest extends TestCase
{
    /** @var ValidatePlatformName $rule */
    private ValidatePlatformName $rule;

    /** @var array|string[] $validPlatformNames */
    private array $validPlatformNames = ValidatePlatformName::VALID_PLATFORMS;

    /** @var array|string[] $invalidPlatformNames */
    private array $invalidPlatformNames = [
        'Water Bottle', 'Tin can telephone', 'Walkie Talkie OS'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidatePlatformName();
    }

    /** @test */
    function valid_platform_names_pass(): void
    {
        foreach($this->validPlatformNames as $validPlatformName) {
            $message = '';

            $this->rule->validate('platform', $validPlatformName, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertEmpty($message, "$validPlatformName did not pass, while it should have!");
        }
    }

    /** @test */
    function invalid_platform_names_dont_pass(): void
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
