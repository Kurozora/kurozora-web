<?php

namespace Laravel\Nova\Tests\Feature;

use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\Fixtures\UserResource;
use Laravel\Nova\Tests\IntegrationTest;

class ResourceTest extends IntegrationTest
{
    public function test_can_use_title_from_json_attribute()
    {
        $user = factory(User::class)->create([
            'meta' => ['name' => 'Taylor Otwell'],
        ]);

        UserResource::$title = 'meta.name';
        $resource = new UserResource($user);

        $this->assertSame('Taylor Otwell', $resource->title());
    }

    public function test_can_use_title_from_array_attribute()
    {
        $user = factory(User::class)->create([
            'meta' => ['Taylor Otwell'],
        ]);

        UserResource::$title = 'meta.0';
        $resource = new UserResource($user);

        $this->assertSame('Taylor Otwell', $resource->title());
    }
}
