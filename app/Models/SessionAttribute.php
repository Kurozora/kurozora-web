<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SessionAttribute extends KModel
{
    use HasFactory;

    // Table name
    const string TABLE_NAME = 'session_attributes';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the model in the session attribute.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The session object belonging to the session attribute.
     *
     * @return BelongsTo
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'model_id', 'id');
    }

    /**
     * The personal access token object belonging to the session attribute.
     *
     * @return BelongsTo
     */
    public function personal_access_token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'model_id', 'token');
    }

    /**
     * Returns the full platform information as a single string.
     *
     * @return string
     */
    function getFullPlatformAttribute(): string
    {
        if ($this->device_model == null || $this->platform == null || $this->platform_version == null) {
            return 'Unknown platform';
        }

        return $this->device_model . ' on ' . $this->platform . ' ' . $this->platform_version;
    }
}
