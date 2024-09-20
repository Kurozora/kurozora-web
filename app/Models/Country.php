<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends KModel
{
    use HasFactory;

    // Table name
    const string TABLE_NAME = 'countries';
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
            'iso_3166_3' => $this->iso_3166_3,
        ];
    }
}
