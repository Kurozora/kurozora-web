<?php

namespace Laravel\Nova\Tests\Controller;

use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Tests\Fixtures\IdFilter;
use Laravel\Nova\Tests\Fixtures\LensFieldValidationAction;
use Laravel\Nova\Tests\Fixtures\NoopAction;
use Laravel\Nova\Tests\Fixtures\NoopInlineAction;
use Laravel\Nova\Tests\Fixtures\Post;
use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\IntegrationTest;

class LensControllerTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    public function test_available_lenses_can_be_retrieved()
    {
        $response = $this->withExceptionHandling()
                        ->get('/nova-api/users/lenses');

        $response->assertStatus(200);
        $this->assertInstanceOf(Lens::class, $response->original[0]);
    }

    public function test_available_lenses_cant_be_retrieved_if_not_authorized_to_view_resource()
    {
        $response = $this->withExceptionHandling()
                        ->get('/nova-api/forbidden-users/lenses');

        $response->assertStatus(403);
    }

    public function test_lens_resources_can_be_retrieved()
    {
        $user = factory(User::class)->create();

        $response = $this->withExceptionHandling()
                        ->get('/nova-api/users/lens/user-lens');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'resources',
            'prev_page_url',
            'next_page_url',
            'softDeletes',
            'per_page_options',
        ]);

        $this->assertEquals([25, 50, 100], $response->original['per_page_options']);

        $this->assertCount(3, $response->original['resources'][0]['actions']);
        $this->assertInstanceOf(NoopAction::class, $response->original['resources'][0]['actions'][0]);
        $this->assertInstanceOf(LensFieldValidationAction::class, $response->original['resources'][0]['actions'][1]);
        $this->assertInstanceOf(NoopInlineAction::class, $response->original['resources'][0]['actions'][2]);
    }

    public function test_lens_that_returns_paginator_can_be_retrieved()
    {
        $response = $this->withExceptionHandling()
                        ->get('/nova-api/users/lens/paginating-user-lens');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'resources',
            'prev_page_url',
            'next_page_url',
            'softDeletes',
        ]);
    }

    public function test_lens_that_doesnt_exist_returns_a_404()
    {
        $response = $this->withExceptionHandling()
                        ->get('/nova-api/users/lens/missing-lens');

        $response->assertStatus(404);
    }

    public function test_lens_cant_be_retrieved_if_not_authorized_to_view_resource()
    {
        $response = $this->withExceptionHandling()
                        ->get('/nova-api/forbidden-users/lens/user-lens');

        $response->assertStatus(403);
    }

    public function test_lenses_can_be_filtered()
    {
        factory(User::class)->create();
        factory(User::class)->create();
        factory(User::class)->create();

        $filters = base64_encode(json_encode([
            [
                'class' => IdFilter::class,
                'value' => 2,
            ],
        ]));

        $response = $this->withExceptionHandling()
                        ->getJson('/nova-api/users/lens/user-lens?filters='.$filters);

        $this->assertEquals(2, $response->original['resources'][0]['id']->value);

        $response->assertJsonCount(1, 'resources');
    }

    public function test_lenses_can_be_sorted()
    {
        factory(Post::class)->create();
        factory(Post::class)->create();
        factory(Post::class)->create();

        $response = $this->withExceptionHandling()
                        ->getJson('/nova-api/posts/lens/post-lens?orderBy=id&orderByDirection=desc');

        $this->assertEquals(3, $response->original['resources'][0]['id']->value);
        $this->assertEquals(2, $response->original['resources'][1]['id']->value);
        $this->assertEquals(1, $response->original['resources'][2]['id']->value);

        $response->assertJsonCount(3, 'resources');
    }

    public function test_lenses_can_be_sorted_using_relation()
    {
        $users = factory(User::class, 3)->create();

        factory(Post::class)->create(['user_id' => $users[0]->id]);
        factory(Post::class)->create(['user_id' => $users[2]->id]);
        factory(Post::class)->create(['user_id' => $users[1]->id]);

        $response = $this->withExceptionHandling()
                        ->getJson('/nova-api/posts/lens/post-lens?orderBy=user_id&orderByDirection=desc');

        $this->assertEquals(2, $response->original['resources'][0]['id']->value);
        $this->assertEquals(3, $response->original['resources'][1]['id']->value);
        $this->assertEquals(1, $response->original['resources'][2]['id']->value);

        $response->assertJsonCount(3, 'resources');
    }
}
