<?php

namespace App\Parsers;

use App\Models\User;
use Xetaio\Mentions\Parser\MentionParser as BaseMentionParser;

class MentionParser extends BaseMentionParser
{
    /**
     * The default configuration used by the parser.
     *
     * @var array
     */
    protected array $defaultConfig = [
        'pool' => 'users',
        'mention' => true,
        'notify' => true,
        'character' => '@',
        'regex' => '/({character}{pattern}{rules})/',
        'regex_replacement' => [
            '{character}' => '@',
            '{pattern}' => '[A-Za-z0-9\-\_]',
            '{rules}' => '{' . User::MINIMUM_SLUG_LENGTH . ',' . User::MAXIMUM_SLUG_LENGTH . '}'
        ]
    ];

    /**
     * Parse a text and determine if it contains mentions. If it does,
     * then we transform the mentions to a markdown link and we notify the user.
     *
     * @param null|string $input The string to parse.
     *
     * @return null|string
     */
    public function parse($input): ?string
    {
        if (empty($input)) {
            return $input;
        }
        $regex = strtr($this->getOption('regex'), $this->getOption('regex_replacement'));

        preg_match_all($regex, $input, $matches);

        $matches = array_unique($matches[0]);
        $matches = array_map([$this, 'mapper'], $matches);

        $matches = $this->removeNullKeys($matches);
        $matches = $this->prepareArray($matches);

        return preg_replace_callback($matches, [$this, 'replace'], $input);
    }

    /**
     * Replace the mention with an HTML link.
     *
     * @param array $match The mention to replace.
     *
     * @return string
     */
    protected function replace(array $match): string
    {
        $character = $this->getOption('character');
        $mention = str($match[0])
            ->trim()
            ->replace($character, '');
        $link = route(config('mentions.pools.' . $this->getOption('pool') . '.route'), $mention);

        return "[$character$mention]($link)";
    }
}
