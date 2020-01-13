<?php

namespace Laravel\Nova\Tests\Controller;

use Laravel\Nova\Tests\Fixtures\Post;
use Laravel\Nova\Tests\Fixtures\User;
use Laravel\Nova\Tests\IntegrationTest;

class CreationControllerTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    public function test_fields_count()
    {
        $response = $this->withExceptionHandling()
            ->getJson('/nova-api/posts/creation-fields');

        $response->assertJsonCount(2, 'fields');

        $response = $this->withExceptionHandling()
            ->getJson('/nova-api/comments/creation-fields');

        $response->assertJsonCount(3, 'fields');
    }

    public function test_related_fields_count()
    {
        $user = factory(User::class)->create();

        $response = $this->withExceptionHandling()
            ->getJson("/nova-api/posts/creation-fields?viaResource=users&viaResourceId={$user->id}&viaRelationship=user");

        $response->assertJsonCount(2, 'fields');
    }

    public function test_morph_related_fields_count()
    {
        $post = factory(Post::class)->create();

        $response = $this->withExceptionHandling()
            ->getJson("/nova-api/comments/creation-fields?viaResource=posts&viaResourceId={$post->id}&viaRelationship=comments");

        $response->assertJsonCount(3, 'fields');
    }

    public function test_related_reverse_belongs_to_fields()
    {
        $user = factory(User::class)->create();

        $response = $this->withExceptionHandling()
            ->getJson("/nova-api/posts/creation-fields?viaResource=users&viaResourceId={$user->id}&viaRelationship=posts");

        $response->assertStatus(200);

        $this->assertTrue($response->decodeResponseJson()['fields'][0]['reverse']);
    }

    public function test_related_reverse_morph_to_fields()
    {
        $post = factory(Post::class)->create();

        $response = $this->withExceptionHandling()
            ->getJson("/nova-api/comments/creation-fields?viaResource=posts&viaResourceId={$post->id}&viaRelationship=comments")
            ->assertOk();

        $this->assertTrue($response->decodeResponseJson()['fields'][0]['reverse']);
        $this->assertFalse($response->decodeResponseJson()['fields'][1]['reverse']);
    }

    public function test_panel_are_returned()
    {
        $this->withoutExceptionHandling()
            ->getJson('/nova-api/panels/creation-fields')
            ->assertJsonCount(3, 'panels')
            ->assertJsonCount(4, 'fields');
    }
}
