<?php

namespace Laravel\Nova\Tests\Feature;

use Brick\Money\Context\CustomContext;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Tests\IntegrationTest;

class CurrencyTest extends IntegrationTest
{
    public function test_computed_currency_field_can_be_resolved_for_display()
    {
        $field = Currency::make('Cost', function () {
            return 777;
        });

        $field->resolveForDisplay((object) []);

        $this->assertEquals('$777.00', $field->value);
    }

    public function test_the_field_is_displayed_with_currency_character()
    {
        $field = Currency::make('Cost');

        $field->resolveForDisplay((object) ['cost' => 200]);

        $this->assertEquals('$200.00', $field->value);
    }

    public function test_the_field_is_displayed_with_symbol()
    {
        $field = Currency::make('Cost')->symbol('USD');

        $field->resolveForDisplay((object) ['cost' => 200]);

        $this->assertEquals('USD 200.00', $field->value);
    }

    public function test_the_field_with_large_value_is_displayed_with_currency_character_on()
    {
        $field = Currency::make('Cost');

        $field->resolveForDisplay((object) ['cost' => 2000000]);

        $this->assertEquals('$2,000,000.00', $field->value);
    }

    public function test_the_field_with_large_value_is_displayed_with_symbol()
    {
        $field = Currency::make('Cost')->symbol('USD');

        $field->resolveForDisplay((object) ['cost' => 2000000]);

        $this->assertEquals('USD 2,000,000.00', $field->value);
    }

    public function test_the_field_can_set_currency()
    {
        $field = Currency::make('Cost')->currency('GBP');

        $field->resolveForDisplay((object) ['cost' => 200]);

        $this->assertEquals('£200.00', $field->value);
    }

    public function test_the_field_can_set_currency_and_symbol()
    {
        $field = Currency::make('Cost')->currency('GBP')->symbol('$');

        $field->resolveForDisplay((object) ['cost' => 200]);

        $this->assertEquals('$200.00', $field->value);
    }

    public function test_the_field_can_change_locale()
    {
        $field = Currency::make('Cost')->currency('EUR')->locale('nl_NL');

        $field->resolveForDisplay((object) ['cost' => 200]);

        $this->assertEquals('€ 200,00', $field->value);
    }

    public function test_the_field_can_change_locale_and_symbol()
    {
        $field = Currency::make('Cost')->currency('EUR')->locale('nl_NL')->symbol('EUR');

        $field->resolveForDisplay((object) ['cost' => 200]);

        $this->assertEquals('EUR 200,00', $field->value);
    }

    public function test_the_field_with_large_value_can_change_locale()
    {
        $field = Currency::make('Cost')->currency('EUR')->locale('nl_NL');

        $field->resolveForDisplay((object) ['cost' => 2000000]);

        $this->assertEquals('€ 2.000.000,00', $field->value);
    }

    public function test_the_field_with_large_value_can_change_locale_and_symbol()
    {
        $field = Currency::make('Cost')->currency('EUR')->locale('nl_NL')->symbol('EUR');

        $field->resolveForDisplay((object) ['cost' => 2000000]);

        $this->assertEquals('EUR 2.000.000,00', $field->value);
    }

    public function test_the_field_handles_null()
    {
        $field = Currency::make('Cost')->nullable();

        $field->resolveForDisplay((object) ['cost' => null]);

        $this->assertNull($field->value);
    }

    public function test_the_field_handles_null_without_setting_as_nullable()
    {
        $field = Currency::make('Cost');

        $field->resolveForDisplay((object) ['cost' => null]);

        $this->assertNull($field->value);
    }

    public function test_the_field_can_use_minor_units()
    {
        $field = Currency::make('Cost')->asMinorUnits();

        $field->resolve((object) ['cost' => 200]);
        $this->assertEquals(200, $field->value);

        $field->resolveForDisplay((object) ['cost' => 200]);
        $this->assertEquals('$2.00', $field->value);
    }

    public function test_the_field_can_have_null_values()
    {
        $field = Currency::make('Cost')
            ->nullable()
            ->asMinorUnits();

        $field->resolve((object) ['cost' => null]);
        $this->assertEquals(null, $field->value);

        $field->resolveForDisplay((object) ['cost' => null]);
        $this->assertEquals(null, $field->value);
    }

    public function test_the_field_can_set_context()
    {
        $field = Currency::make('Cost')->context(new CustomContext(8));

        $field->resolveForDisplay((object) ['cost' => 200.12345678]);

        $this->assertEquals('$200.12345678', $field->value);
    }

    public function test_the_field_is_filled_correctly_using_minor_units()
    {
        $request = NovaRequest::create('/nova-api/users', 'POST', [
            'editing' => true,
            'editMode' => 'create',
            'cost' => '2500',
        ]);

        $model = new \stdClass();

        $field = Currency::make('Cost')->asMinorUnits()->fill($request, $model);

        $this->assertEquals(2500, $model->cost);
    }

    public function test_the_field_is_filled_correctly_with_null_when_using_minor_units()
    {
        $request = NovaRequest::create('/nova-api/users', 'POST', [
            'editing' => true,
            'editMode' => 'create',
            'cost' => null,
        ]);

        $model = new \stdClass();

        $field = Currency::make('Cost')->asMinorUnits()->fill($request, $model);

        $this->assertNull($model->cost);
    }
}
