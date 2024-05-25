<?php

namespace App\Models;

use App\Traits\Model\HasSessionAttribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasSessionAttribute;

    // Table name
    const string TABLE_NAME = 'personal_access_tokens';
    protected $table = self::TABLE_NAME;

    /**
     * The tokenable relationship of the access token.
     *
     * @return MorphTo
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user relationship of the access token.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tokenable_id', 'id');
    }

    /**
     * The session attribute of the personal access token.
     *
     * @return morphOne
     */
    public function session_attribute(): MorphOne
    {
        return $this->morphOne(SessionAttribute::class, 'model', localKey: 'token');
    }
}
