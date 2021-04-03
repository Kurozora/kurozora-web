<?php

namespace Laravel\Nova\Tests\Feature;

use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceDetailRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Tests\IntegrationTest;

class StackTest extends IntegrationTest
{
    public function test_stack_fields_resolve_the_correct_values()
    {
        $field = Stack::make('Details', [
            $line = Line::make('Name'),
            $text = Text::make('Subtitle'),
        ]);

        $field->resolveForDisplay((object) [
            'name' => 'David Hemphill',
        ]);

        $this->assertSubset([
            'lines' => [
                $line,
                $text,
            ],
        ], $field->jsonSerialize());
    }

    public function test_stack_fields_resolve_the_correct_values_with_hidden_column_from_index()
    {
        $this->instance(NovaRequest::class, ResourceIndexRequest::create('GET', '/'));

        $field = Stack::make('Details', [
            $line = Line::make('Name'),
            $text = Text::make('Subtitle')->hideFromIndex(),
        ]);

        $field->resolveForDisplay((object) [
            'name' => 'David Hemphill',
        ]);

        $this->assertSubset([
            'lines' => [
                $line,
            ],
        ], $field->jsonSerialize());
    }

    public function test_stack_fields_resolve_the_correct_values_with_hidden_column_from_detail()
    {
        $this->instance(NovaRequest::class, ResourceDetailRequest::create('GET', '/'));

        $field = Stack::make('Details', [
            $line = Line::make('Name')->hideFromDetail(),
            $text = Text::make('Subtitle'),
        ]);

        $field->resolveForDisplay((object) [
            'name' => 'David Hemphill',
        ]);

        $this->assertSubset([
            'lines' => [
                $text,
            ],
        ], $field->jsonSerialize());
    }

    public function test_stack_items_resolve_correctly()
    {
        $line = Line::make('Name');

        $this->assertSubset([
            'classes' => [Line::$classes['large']],
        ], $line->jsonSerialize());

        // ----------------------------------------- //

        $line = Line::make('Name')->asSubTitle();

        $this->assertSubset([
            'classes' => [Line::$classes['medium']],
        ], $line->jsonSerialize());

        // ----------------------------------------- //

        $line = Line::make('Name')->asBase();

        $this->assertSubset([
            'classes' => [Line::$classes['large']],
        ], $line->jsonSerialize());

        // ----------------------------------------- //

        $line = Line::make('Name')->asSmall();

        $this->assertSubset([
            'classes' => [Line::$classes['small']],
        ], $line->jsonSerialize());

        // ----------------------------------------- //

        $line = Line::make('Name')->extraClasses('italic');

        $this->assertSubset([
            'classes' => [Line::$classes['large'], 'italic'],
        ], $line->jsonSerialize());
    }
}
