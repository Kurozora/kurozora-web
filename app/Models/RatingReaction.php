<?php

namespace App\Models;

use App\Enums\RatingReactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingReaction extends Model
{
    // Table name
    const string TABLE_NAME = 'review_reactions';
    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'rating_id',
        'user_id',
        'type',
    ];

    protected $casts = [
        'type' => RatingReactionType::class,
    ];

    public function rating(): BelongsTo
    {
        return $this->belongsTo(MediaRating::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
