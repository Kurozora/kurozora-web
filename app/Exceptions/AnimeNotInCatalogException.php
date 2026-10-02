<?php

namespace App\Exceptions;

use Exception;

class AnimeNotInCatalogException extends Exception
{
    /**
     * The MAL id the catalog has no entry for.
     *
     * @var int|null $malID
     */
    public ?int $malID;

    /**
     * Create a new exception instance.
     *
     * @param int|null $malID
     */
    public function __construct(?int $malID = null)
    {
        parent::__construct('The anime isn’t in the catalog yet.');

        $this->malID = $malID;
    }
}
