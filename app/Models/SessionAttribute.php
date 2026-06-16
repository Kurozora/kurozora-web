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
    public function personalAccessToken(): BelongsTo
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
        $system = collect([$this->platform, $this->platform_version])
            ->filter(fn ($part) => filled($part))
            ->join(' ');

        if (filled($this->device_model) && filled($system)) {
            return $this->device_model . ' on ' . $system;
        }

        return filled($system) ? $system : __('Unknown platform');
    }

    /**
     * Returns the location as a single string.
     *
     * @return string
     */
    function getFullLocationAttribute(): string
    {
        return collect([$this->city, $this->country])
            ->filter(fn ($part) => filled($part) && $part !== 'Unknown')
            ->join(', ');
    }
}
