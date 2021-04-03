<?php

namespace Laravel\Nova\Tests\Controller;

use Laravel\Nova\Tests\Fixtures\Category;
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

        $response->assertJsonCount(3, 'fields');

        $response = $this->withExceptionHandling()
            ->getJson('/nova-api/comments/creation-fields');

        $response->assertJsonCount(3, 'fields');
    }

    public function test_related_fields_count()
    {
        $response = $this->withoutExceptionHandling()
            ->getJson('/nova-api/comments/creation-fields');

        $response->assertJsonCount(3, 'fields');
        $response->assertJson([
            'fields' => [
                1 => ['component' => 'morph-to-field'],
                2 => ['component' => 'belongs-to-field'],
                3 => ['component' => 'text-field'],
            ],
        ]);
    }

    public function test_related_fields_count_via_relation()
    {
        $user = factory(User::class)->create();

        $response = $this->withExceptionHandling()
            ->getJson("/nova-api/posts/creation-fields?viaResource=users&viaResourceId={$user->id}&viaRelationship=user");

        $response->assertJsonCount(3, 'fields');
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

    public function test_reverse_can_be_true_if_two_relation_fields_share_the_same_related_resource_class()
    {
        $parent = factory(Category::class)->create(['title' => 'Parent']);
        $category = factory(Category::class)->create(['parent_id' => $parent->getKey(), 'title' => 'Child']);

        $params = http_build_query([
            'editing' => true,
            'editMode' => 'create',
            'viaResource' => 'categories',
            'viaResourceId' => $parent->getKey(),
            'viaRelationship' => 'children',
        ]);

        $response = $this->withExceptionHandling()
                        ->getJson("/nova-api/categories/creation-fields?{$params}")
                        ->assertOk()
                        ->assertJson([
                            'fields' => [
                                [
                                    'label' => 'Category Resources',
                                    'reverse' => true,
                                ],
                            ],
                        ]);
    }

    public function test_panel_are_returned()
    {
        $this->withoutExceptionHandling()
            ->getJson('/nova-api/panels/creation-fields')
            ->assertJsonCount(3, 'panels')
            ->assertJsonCount(4, 'fields');
    }

    public function test_creation_fields_for_custom_model_attributes_do_not_crash()
    {
        $this->withoutExceptionHandling()
            ->getJson('/nova-api/wheels/creation-fields')
            ->assertOk();
    }

    public function test_creation_fields_can_have_default_values()
    {
        $response = $this->withExceptionHandling()
            ->getJson('/nova-api/posts/creation-fields?editing=true&editMode=create');

        $response->assertJsonCount(3, 'fields');

        $this->assertEquals('default-slug', $response->decodeResponseJson()['fields'][3]['value']);
    }

    public function test_creation_fields_can_use_model_default_attributes()
    {
        $response = $this->withExceptionHandling()
            ->getJson('/nova-api/users/creation-fields?editing=true&editMode=create');

        $this->assertEquals('Anonymous User', $response->original['fields']->where('attribute', 'name')->first()->value);
    }
}

class Author extends User
{
    protected $table = 'users';

    public function getPostAttribute()
    {
        return $this->belongsTo(Post::class);
    }
}
