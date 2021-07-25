<?php

namespace App\Models;

use App\Rules\ValidatePlatformName;
use App\Rules\ValidatePlatformVersion;
use App\Rules\ValidateVendorName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'sessions';
    protected $table = self::TABLE_NAME;

    // How many days sessions are valid for by default
    const VALID_FOR_DAYS = 10;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'date',
        'last_activity_at' => 'date',
    ];

    /**
     * Returns the user that owns the session.
     *
     * @return BelongsTo
     */
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns whether or not the session is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    /**
     * Returns the platform information in a human readable format.
     *
     * @return string
     */
    function humanReadablePlatform(): string
    {
        if ($this->device_model == null ||
            $this->platform == null ||
            $this->platform_version == null)
            return 'Unknown platform';

        return $this->device_model . ' on ' . $this->platform . ' ' . $this->platform_version;
    }

    /**
     * Returns the validation rules for validating platform fields.
     *
     * @return array
     */
    static function platformRules(): array
    {
        return [
            'platform'          => ['bail', 'required', new ValidatePlatformName],
            'platform_version'  => ['bail', 'required', new ValidatePlatformVersion],
            'device_vendor'     => ['bail', 'required', new ValidateVendorName],
            'device_model'      => ['bail', 'required', 'string', 'max:50']
        ];
    }
}
