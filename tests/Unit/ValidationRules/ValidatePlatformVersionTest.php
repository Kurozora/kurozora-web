<?php

namespace Tests\Unit\ValidationRules;

use App\Rules\ValidatePlatformVersion;
use Tests\TestCase;

class ValidatePlatformVersionTest extends TestCase
{
    /** @var ValidatePlatformVersion $rule */
    private $rule;

    private $validPlatformVersions = [
        '1', '1.2', '1.2.0', '5.0', '902.3', '21.23', '3.455'
    ];

    private $invalidPlatformVersions = [
        'v1.0', '2.0-rc', 'best version', 'cool', 'xp', '-12.1', '-1'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidatePlatformVersion();
    }

    /** @test */
    function valid_platform_versions_pass()
    {
        foreach($this->validPlatformVersions as $validPlatformVersion)
            $this->assertTrue($this->rule->passes('platform_version', $validPlatformVersion), "$validPlatformVersion did not pass, while it should have!");
    }

    /** @test */
    function invalid_platform_versions_dont_pass()
    {
        foreach($this->invalidPlatformVersions as $invalidPlatformVersion)
            $this->assertFalse($this->rule->passes('platform_version', $invalidPlatformVersion), "$invalidPlatformVersion passed, while it should not have!");
    }
}
