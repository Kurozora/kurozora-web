<?php

namespace App\Helpers;

class OptionsBag
{
    private $data;

    function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the value of an option, or the default ..
     * .. value if the option is not present.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get($key, $default = null)
    {
        if (isset($this->data[$key]))
            return $this->data[$key];
        else return $default;
    }
}
