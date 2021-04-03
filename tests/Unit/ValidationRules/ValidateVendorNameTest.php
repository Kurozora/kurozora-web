<?php

namespace Tests\Unit\ValidationRules;

use App\Rules\ValidateVendorName;
use Tests\TestCase;

class ValidateVendorNameTest extends TestCase
{
    /** @var ValidateVendorName $rule */
    private $rule;

    private $validVendorNames = [
        'Apple'
    ];

    private $invalidVendorNames = [
        'Samsung', 'Huawei', 'HP', 'Lenovo', 'Google', 'Acer', 'Asus', 'Coca Cola'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidateVendorName();
    }

    /** @test */
    function valid_vendor_names_pass()
    {
        foreach($this->validVendorNames as $validVendorName)
            $this->assertTrue($this->rule->passes('device_vendor', $validVendorName), "$validVendorName did not pass, while it should have!");
    }

    /** @test */
    function invalid_vendor_names_dont_pass()
    {
        foreach($this->invalidVendorNames as $invalidVendorName)
            $this->assertFalse($this->rule->passes('device_vendor', $invalidVendorName), "$invalidVendorName passed, while it should not have!");
    }
}
