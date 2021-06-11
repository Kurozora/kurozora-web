<?php

namespace Laravel\Nova;

class Util
{
    /**
     * Convert large integer higher than Number.MAX_SAFE_INTEGER to string.
     *
     * https://stackoverflow.com/questions/47188449/json-max-int-number/47188576
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function safeInt($value)
    {
        if (is_int($value) && $value >= 9007199254740991) {
            return (string) $value;
        }

        return $value;
    }
}
