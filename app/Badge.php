<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    // Table name
    const TABLE_NAME = 'badge';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['text', 'textColor', 'backgroundColor', 'description'];

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
