<?php

namespace Laravel\Nova\Tests\Feature;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Tests\Fixtures\Role;
use Laravel\Nova\Tests\Fixtures\RoleResource;
use Laravel\Nova\Tests\IntegrationTest;

class IDFieldTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Field::$customComponents = [];
    }

    public function test_resolve_field_as_int()
    {
        $field = ID::make('ID');

        $field->resolve((new Role)->forceFill(['id' => 5]));

        $this->assertSame(5, $field->value);
    }

    public function test_resolve_field_as_big_int()
    {
        $field = ID::make('ID')->asBigInt();

        $field->resolve((new Role)->forceFill(['id' => 15]));

        $this->assertSame('15', $field->value);
    }

    public function test_resolve_field_from_model()
    {
        $field = ID::forModel((new Role)->forceFill(['id' => 25]));

        $this->assertInstanceOf(ID::class, $field);
        $this->assertSame(25, $field->value);
    }

    public function test_resolve_field_from_resource()
    {
        $resource = new RoleResource((new Role)->forceFill(['id' => 35]));

        $this->app->instance(
            NovaRequest::class, NovaRequest::create('/', 'GET', [])
        );

        $field = ID::forResource($resource);

        $this->assertInstanceOf(ID::class, $field);
        $this->assertSame(35, $field->value);
    }

    public function test_resolve_field_from_resource_without_model_and_nullable()
    {
        $resource = new class(new Role) extends RoleResource {
            public function fields(Request $request)
            {
                return [
                    ID::make('ID')->nullable(),
                ];
            }
        };

        $this->app->instance(
            NovaRequest::class, NovaRequest::create('/', 'GET', [])
        );

        $field = ID::forResource($resource);

        $this->assertInstanceOf(ID::class, $field);
        $this->assertNull($field->value);
    }

    public function test_resolve_field_from_resource_without_model_and_not_nullable()
    {
        $resource = new RoleResource(null);

        $this->app->instance(
            NovaRequest::class, NovaRequest::create('/', 'GET', [])
        );

        $field = ID::forResource($resource);

        $this->assertNull($field);
    }
}
