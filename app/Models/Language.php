<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends KModel
{
    use HasFactory;

    // Table name
    const string TABLE_NAME = 'languages';
    protected $table = self::TABLE_NAME;

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'iso_639_3' => $this->iso_639_3,
        ];
    }
}
