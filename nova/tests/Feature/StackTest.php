<?php

namespace Laravel\Nova\Tests\Feature;

use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
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
