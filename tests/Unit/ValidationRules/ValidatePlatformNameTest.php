<?php

namespace Tests\Unit\ValidationRules;

use App\Rules\ValidatePlatformName;
use Tests\TestCase;

class ValidatePlatformNameTest extends TestCase
{
    /** @var ValidatePlatformName $rule */
    private $rule;

    private $validPlatformNames = [
        'iOS', 'Android', 'Web', 'Console', 'macOS', 'iPadOS', 'tvOS', 'watchOS'
    ];

    private $invalidPlatformNames = [
        'Windows', 'Linux', 'Water Bottle', 'Tin can telephone', 'Walkie Talkie OS'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidatePlatformName();
    }

    /** @test */
    function valid_platform_names_pass()
    {
        foreach($this->validPlatformNames as $validPlatformName)
            $this->assertTrue($this->rule->passes('platform', $validPlatformName), "$validPlatformName did not pass, while it should have!");
    }

    /** @test */
    function invalid_platform_names_dont_pass()
    {
        foreach($this->invalidPlatformNames as $invalidPlatformName)
            $this->assertFalse($this->rule->passes('platform', $invalidPlatformName), "$invalidPlatformName passed, while it should not have!");
    }
}
