<?php

namespace Laravel\Nova\Fields;

use DateTimeInterface;
use Exception;
use Laravel\Nova\Http\Requests\NovaRequest;

class DateTime extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'date-time';

    /**
     * Cast format from DateTime instance.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  mixed|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback ?? function ($value) {
            if (! is_null($value)) {
                if ($value instanceof DateTimeInterface) {
                    return $value->format($this->dateFormat);
                }

                throw new Exception("DateTime field must cast to 'datetime' in Eloquent model.");
            }
        });
    }

    /**
     * Set the first day of the week.
     *
     * @param  int  $day
     * @return $this
     */
    public function firstDayOfWeek($day)
    {
        return $this->withMeta([__FUNCTION__ => $day]);
    }

    /**
     * Set the date format (Moment.js) that should be used to display the date.
     *
     * @param  string  $format
     * @return $this
     */
    public function format($format)
    {
        return $this->withMeta([__FUNCTION__ => $format]);
    }

    /**
     * Set the date format (flatpickr.js) that should be used in the input field (picker).
     *
     * @param  string  $format
     * @return $this
     */
    public function pickerFormat($format)
    {
        return $this->withMeta([__FUNCTION__ => $format]);
    }

    /**
     * Set a readable date format, that should be used to display the date to the user.
     *
     * @param  string  $format
     * @return $this
     */
    public function pickerDisplayFormat($format)
    {
        return $this->withMeta([__FUNCTION__ => $format]);
    }

    /**
     * Set picker hour increment.
     *
     * @param  int  $increment
     * @return $this
     */
    public function incrementPickerHourBy($increment)
    {
        $increment = intval($increment);

        if ($increment > 0 && $increment < 24) {
            return $this->withMeta(['pickerHourIncrement' => $increment]);
        }

        return $this;
    }

    /**
     * Set picker minute increment.
     *
     * @param  int  $increment
     * @return $this
     */
    public function incrementPickerMinuteBy($increment)
    {
        $increment = intval($increment);

        if ($increment > 0 && $increment < 60) {
            return $this->withMeta(['pickerMinuteIncrement' => $increment]);
        }

        return $this;
    }

    /**
     * Resolve the default value for the field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return string
     */
    protected function resolveDefaultValue(NovaRequest $request)
    {
        $value = parent::resolveDefaultValue($request);

        if ($value instanceof DateTimeInterface) {
            return $value->format($this->dateFormat);
        }

        return $value;
    }
}
