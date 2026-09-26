<?php

namespace App\Models;

use App\Enums\PersonRelationshipType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonRelationship extends KModel
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'person_relationships';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'type' => PersonRelationshipType::class,
            'started_on' => 'date',
            'ended_on' => 'date',
        ];
    }

    /**
     * The person the relationship belongs to.
     *
     * @return BelongsTo
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * The related person of the relationship.
     *
     * @return BelongsTo
     */
    public function relatedPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'related_person_id');
    }
}
