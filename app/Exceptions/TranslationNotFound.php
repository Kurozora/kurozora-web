<?php

namespace App\Exceptions;

use Exception;

class TranslationNotFound extends Exception
{
    public static function make(string $key, $model): static
    {
        return new static("Cannot find translations for `{$key}` locale.");
    }
}
