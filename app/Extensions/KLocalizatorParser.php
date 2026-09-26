<?php

namespace App\Extensions;

use Amirami\Localizator\Services\Parser;

class KLocalizatorParser extends Parser
{
    /**
     * Returns the pattern that extracts the whole first string argument of the given function.
     *
     * @param string $function
     *
     * @return string
     */
    protected function searchPattern(string $function): string
    {
        return '/(' . preg_quote($function, '/') . ')\(\s*(?|\'((?:\\\\.|[^\'\\\\])*)\'|"((?:\\\\.|[^"\\\\])*)")\s*[),]/s';
    }
}
