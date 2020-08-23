<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class APIError extends Model
{
    /**
     * A unique identifier for this occurrence of the error.
     *
     * @var int
     */
    public int $id;

    /**
     * The [HTTP Status Code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status) for this problem.
     *
     * @var int
     */
    public int $status;

    /**
     * A short description of the problem.
     *
     * @var string
     */
    public string $title;

    /**
     * A long description of the problem.
     *
     * @var string
     */
    public string $detail;
}
