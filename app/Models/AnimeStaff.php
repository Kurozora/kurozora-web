<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeStaff extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'anime_staff';
    protected $table = self::TABLE_NAME;

    /**
     * The anime relationship of anime staff.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * The person relationship of anime staff.
     *
     * @return BelongsTo
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * The staff role relationship of anime staff.
     *
     * @return BelongsTo
     */
    public function staff_role(): BelongsTo
    {
        return $this->belongsTo(StaffRole::class);
    }
}
