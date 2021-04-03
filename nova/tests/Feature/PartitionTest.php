<?php

namespace Laravel\Nova\Tests\Feature;

use Illuminate\Support\Str;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Tests\IntegrationTest;

class PartitionTest extends IntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_partition_keys_and_values_are_formatted_correctly_when_serialized()
    {
        $result = new PartitionResult(['Monthly' => 60, 'Yearly' => 90]);

        $this->assertEquals([
            'value' => [
                ['label' => 'Monthly', 'value' => 60],
                ['label' => 'Yearly', 'value' => 90],
            ],
        ], $result->jsonSerialize());
    }

    public function test_colors_are_present_in_results_when_set_with_string_labels()
    {
        $result = new PartitionResult(['Monthly' => 60, 'Yearly' => 90]);
        $result->colors(['Monthly' => '#fff', 'Yearly' => '#000']);

        $this->assertEquals([
            'value' => [
                ['label' => 'Monthly', 'value' => 60, 'color' => '#fff'],
                ['label' => 'Yearly', 'value' => 90, 'color' => '#000'],
            ],
        ], $result->jsonSerialize());
    }

    public function test_colors_are_present_in_results_when_set_with_string_labels_with_custom_label()
    {
        $result = new PartitionResult(['weekly' => 10, 'monthly' => 60, 'yearly' => 90]);
        $result->label(function ($label) {
            return Str::title($label);
        })->colors([
            'weekly' => '#fff',
            'monthly' => '#000',
        ]);

        $this->assertEquals([
            'value' => [
                ['label' => 'Weekly', 'value' => 10, 'color' => '#fff'],
                ['label' => 'Monthly', 'value' => 60, 'color' => '#000'],
                ['label' => 'Yearly', 'value' => 90],
            ],
        ], $result->jsonSerialize());
    }

    public function test_colors_are_present_in_results_when_provided_color_map()
    {
        $result = new PartitionResult(['Weekly' => 10, 'Monthly' => 60, 'Yearly' => 90]);
        $result->colors(['#fff', '#000']);

        $this->assertEquals([
            'value' => [
                ['label' => 'Weekly', 'value' => 10, 'color' => '#fff'],
                ['label' => 'Monthly', 'value' => 60, 'color' => '#000'],
                ['label' => 'Yearly', 'value' => 90, 'color' => '#fff'],
            ],
        ], $result->jsonSerialize());
    }

    public function test_colors_are_present_in_results_when_set_with_string_labels_with_custom_label_after_serialization()
    {
        $result = new PartitionResult(['weekly' => 10, 'monthly' => 60, 'yearly' => 90]);
        $result->label(function ($label) {
            return Str::title($label);
        })->colors([
            'weekly' => '#fff',
            'monthly' => '#000',
        ]);

        $this->assertEquals([
            'value' => [
                ['label' => 'Weekly', 'value' => 10, 'color' => '#fff'],
                ['label' => 'Monthly', 'value' => 60, 'color' => '#000'],
                ['label' => 'Yearly', 'value' => 90],
            ],
        ], unserialize(serialize($result))->jsonSerialize());
    }
}
