<?php

namespace Laravel\Nova\Tests\Feature;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceDetailRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Tests\Fixtures\NoopAction;
use Laravel\Nova\Tests\IntegrationTest;

class TextTest extends IntegrationTest
{
    public function test_field_does_not_output_suggestions_for_index_request()
    {
        $this->instance(NovaRequest::class, ResourceIndexRequest::create('/'));

        $field = Text::make('Title')
            ->suggestions([
                'Foobar',
            ]);

        $field->resolve((object) ['title' => 'Hello World'], 'title');
        $this->assertEquals('Hello World', $field->value);

        $this->assertSame('{"attribute":"title","component":"text-field","helpText":null,"indexName":"Title","name":"Title","nullable":false,"panel":null,"prefixComponent":true,"readonly":false,"required":false,"sortable":false,"sortableUriKey":"title","stacked":false,"textAlign":"left","validationKey":"title","value":"Hello World"}', json_encode($field));
    }

    public function test_field_does_not_output_suggestions_for_detail_request()
    {
        $this->instance(NovaRequest::class, ResourceDetailRequest::create('/'));

        $field = Text::make('Title')
            ->suggestions([
                'Foobar',
            ]);

        $field->resolve((object) ['title' => 'Hello World'], 'title');
        $this->assertEquals('Hello World', $field->value);

        $this->assertSame('{"attribute":"title","component":"text-field","helpText":null,"indexName":"Title","name":"Title","nullable":false,"panel":null,"prefixComponent":true,"readonly":false,"required":false,"sortable":false,"sortableUriKey":"title","stacked":false,"textAlign":"left","validationKey":"title","value":"Hello World"}', json_encode($field));
    }

    public function test_field_output_suggestions_for_create_request()
    {
        $this->instance(NovaRequest::class, NovaRequest::create('/', 'GET', [
            'editing' => true,
            'editMode' => 'create',
        ]));

        $field = Text::make('Title')
            ->suggestions([
                'Foobar',
            ]);

        $field->resolve((object) ['title' => 'Hello World'], 'title');
        $this->assertEquals('Hello World', $field->value);

        $this->assertSame('{"attribute":"title","component":"text-field","helpText":null,"indexName":"Title","name":"Title","nullable":false,"panel":null,"prefixComponent":true,"readonly":false,"required":false,"sortable":false,"sortableUriKey":"title","stacked":false,"textAlign":"left","validationKey":"title","value":"Hello World","suggestions":["Foobar"]}', json_encode($field));
    }

    public function test_field_output_suggestions_for_update_request()
    {
        $this->instance(NovaRequest::class, NovaRequest::create('/', 'POST', [
            'editing' => true,
            'editMode' => 'update',
        ]));

        $field = Text::make('Title')
            ->suggestions([
                'Foobar',
            ]);

        $field->resolve((object) ['title' => 'Hello World'], 'title');
        $this->assertEquals('Hello World', $field->value);

        $this->assertSame('{"attribute":"title","component":"text-field","helpText":null,"indexName":"Title","name":"Title","nullable":false,"panel":null,"prefixComponent":true,"readonly":false,"required":false,"sortable":false,"sortableUriKey":"title","stacked":false,"textAlign":"left","validationKey":"title","value":"Hello World","suggestions":["Foobar"]}', json_encode($field));
    }

    public function test_field_does_output_suggestions_for_action_request()
    {
        $this->instance(NovaRequest::class, NovaRequest::create('/nova-api/users/actions', 'GET', [
            'resources' => 1,
            'test' => 'Taylor Otwell',
            'callback' => '',
        ]));

        $field = Text::make('Title')
            ->suggestions([
                'Foobar',
            ]);

        $field->resolve((object) ['title' => 'Hello World'], 'title');
        $this->assertEquals('Hello World', $field->value);

        $this->assertSame('{"attribute":"title","component":"text-field","helpText":null,"indexName":"Title","name":"Title","nullable":false,"panel":null,"prefixComponent":true,"readonly":false,"required":false,"sortable":false,"sortableUriKey":"title","stacked":false,"textAlign":"left","validationKey":"title","value":"Hello World","suggestions":["Foobar"]}', json_encode($field));
    }

    public function test_field_does_not_output_suggestions_for_executing_action_request()
    {
        $this->instance(NovaRequest::class, ActionRequest::create('/nova-api/users/action?action='.(new NoopAction)->uriKey(), 'POST', [
            'resources' => 1,
            'test' => 'Taylor Otwell',
            'callback' => '',
        ]));

        $field = Text::make('Title')
            ->suggestions([
                'Foobar',
            ]);

        $field->resolve((object) ['title' => 'Hello World'], 'title');
        $this->assertEquals('Hello World', $field->value);

        $this->assertSame('{"attribute":"title","component":"text-field","helpText":null,"indexName":"Title","name":"Title","nullable":false,"panel":null,"prefixComponent":true,"readonly":false,"required":false,"sortable":false,"sortableUriKey":"title","stacked":false,"textAlign":"left","validationKey":"title","value":"Hello World"}', json_encode($field));
    }
}
