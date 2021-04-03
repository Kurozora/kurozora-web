<?php

namespace Laravel\Nova\Tests\Feature;

use Laravel\Nova\Tests\Fixtures\Snippet;
use Laravel\Nova\Tests\IntegrationTest;

class CodeFieldTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    public function test_code_field_can_be_resolved()
    {
        $snippet = factory(Snippet::class)->create(['code' => 'ls -l']);

        $params = http_build_query([
            'editing' => true,
            'editMode' => 'update',
            'viaResource' => '',
            'viaRelationship' => '',
        ]);

        $this->withExceptionHandling()
            ->getJson('/nova-api/snippets/'.$snippet->getKey().'/update-fields?'.$params)
            ->assertOk();
    }

    public function test_code_field_can_store_json_values()
    {
        $json = json_encode([
            'key' => 'value',
        ]);

        $this->withoutExceptionHandling()
            ->postJson('/nova-api/snippets', [
                'name' => 'Code',
                'code' => $json,
            ])
            ->assertStatus(201);

        $snippet = Snippet::first();

        $this
            ->withExceptionHandling()
            ->getJson('/nova-api/snippets/'.$snippet->getKey())
            ->assertJson([
                'resource' => [
                    'fields' => [
                        1 => [
                            'value' => '{
    "key": "value"
}',
                        ],
                    ],
                ],
            ]);
    }

    public function test_code_field_can_store_primitive_json_values()
    {
        $this->withoutExceptionHandling()
            ->postJson('/nova-api/snippets', [
                'name' => 'Code',
                'code' => '"This is a primitive value"',
            ])
            ->assertStatus(201);

        $snippet = Snippet::first();

        $this
            ->withExceptionHandling()
            ->getJson('/nova-api/snippets/'.$snippet->getKey())
            ->assertJson([
                'resource' => [
                    'fields' => [
                        1 => [
                            'value' => '"This is a primitive value"',
                        ],
                    ],
                ],
            ]);
    }
}
