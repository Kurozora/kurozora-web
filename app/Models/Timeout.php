<?php

namespace App\Models;

use App\Enums\TimeoutReason;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Timeout extends KModel
{
    // Table name
    const string TABLE_NAME = 'timeouts';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast to native types.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'reason_key' => TimeoutReason::class,
            'is_permanent' => 'bool',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'expiry_notified_at' => 'datetime',
        ];
    }

    /**
     * Returns the user the timeout was issued against.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the staff member who issued the timeout.
     *
     * @return BelongsTo
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_id');
    }

    /**
     * Returns the staff member who revoked the timeout, if any.
     *
     * @return BelongsTo
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_id');
    }

    /**
     * Returns the user's appeal for this timeout, if one was filed.
     *
     * @return HasOne
     */
    public function appeal(): HasOne
    {
        return $this->hasOne(TimeoutAppeal::class);
    }

    /**
     * Restricts the query to timeouts that are currently in effect.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where(function (Builder $query) {
                $query->where('is_permanent', true)
                    ->orWhere('expires_at', '>', Carbon::now());
            });
    }

    /**
     * Indicates whether the timeout is currently in effect.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->is_permanent) {
            return true;
        }

        return $this->expires_at !== null && $this->expires_at->isFuture();
    }
}
