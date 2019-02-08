<?php

namespace App;

class Badge extends KModel
{
    // Table name
    const TABLE_NAME = 'badge';
    protected $table = self::TABLE_NAME;

    /**
     * Formats the badge for a JSON response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'id'                => $this->id,
            'text'              => $this->text,
            'textColor'         => $this->textColor,
            'backgroundColor'   => $this->backgroundColor,
            'description'       => $this->description
        ];
    }
}
