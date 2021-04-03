<?php

namespace Laravel\Nova\Fields;

use DateTimeInterface;
use Exception;

class DateTime extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'date-time';

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
                    return $value->format('Y-m-d H:i:s.u');
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
}
