<?php

namespace Laravel\Nova\Tests\Feature;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Tests\IntegrationTest;

class ToolTest extends IntegrationTest
{
    public function setUp() : void
    {
        parent::setUp();
    }

    public function test_authorization_callback_is_executed()
    {
        Nova::tools([
            new class extends Tool {
                public function authorize(Request $request)
                {
                    return false;
                }
            },
        ]);

        $this->assertCount(0, Nova::availableTools(Request::create('/')));
    }
}
