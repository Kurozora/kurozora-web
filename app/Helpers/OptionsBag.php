<?php

namespace App\Helpers;

class OptionsBag
{
    /**
     * The data in the bag.
     *
     * @var array
     */
    private array $data;

    function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the value of an option, or the default value if the option is not present.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
