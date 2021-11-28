<?php

namespace App\Models;

use App\Rules\ValidatePlatformName;
use App\Rules\ValidatePlatformVersion;
use App\Rules\ValidateVendorName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Psr\Container\ContainerExceptionInterface;

class Session extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'sessions';
    protected $table = self::TABLE_NAME;

    // How many days sessions are valid for by default
    const VALID_FOR_DAYS = 10;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'date',
    ];

    public function setApnDeviceTokenAttribute(?string $apnDeviceToken)
    {
        session()->put('apn_device_token', $apnDeviceToken);
    }

    public function getApnDeviceTokenAttribute(): ?string
    {
        try {
            return session()->get('apn_device_token');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setSecretAttribute(?string $secret)
    {
        session()->put('secret', $secret);
    }

    public function getSecretAttribute(): ?string
    {
        try {
            return session()->get('secret');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setIpAddressAttribute(?string $secret)
    {
        session()->put('ip_address', $secret);
    }

    public function getIpAddressAttribute(): ?string
    {
        try {
            return session()->get('ip_address');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setExpiresAtAttribute(?Carbon $expiresAt)
    {
        session()->put('expires_at', $expiresAt);
    }

    public function getExpiresAtAttribute(): ?Carbon
    {
        try {
            return session()->get('expires_at');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setPlatformAttribute(?string $platform)
    {
        session()->put('platform', $platform);
    }

    public function getPlatformAttribute(): ?string
    {
        try {
            return session()->get('platform');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setPlatformVersionAttribute(?string $platformVersion) {
        session()->put('platform_version', $platformVersion);
    }

    public function getPlatformVersionAttribute(): ?string
    {
        try {
            return session()->get('platform_version');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setDeviceVendorAttribute(?string $deviceVendor)
    {
        session()->put('device_vendor', $deviceVendor);
    }

    public function getDeviceVendorAttribute(): ?string
    {
        try {
            return session()->get('device_vendor');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setDeviceModelAttribute(?string $deviceModel)
    {
        session()->put('device_model', $deviceModel);
    }

    public function getDeviceModelAttribute(): ?string
    {
        try {
            return session()->get('device_model');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setCityAttribute(?string $city)
    {
        session()->put('city', $city);
    }

    public function getCityAttribute(): ?string
    {
        try {
            return session()->get('city');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setRegionAttribute(?string $regio)
    {
        session()->put('region', $regio);
    }

    public function getRegionAttribute(): ?string
    {
        try {
            return session()->get('region');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setCountryAttribute(?string $country)
    {
        session()->put('country', $country);
    }

    public function getCountryAttribute(): ?string
    {
        try {
            return session()->get('country');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setLatitudeAttribute(?float $latitude)
    {
        session()->put('latitude', $latitude);
    }

    public function getLatitudeAttribute(): ?float
    {
        try {
            return session()->get('latitude');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function setLongitudeAttribute(?float $longitude) {
        session()->put('longitude', $longitude);
    }

    public function getLongitudeAttribute(): ?float
    {
        try {
            return session()->get('longitude');
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

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
     * Returns whether the session is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    /**
     * Returns the platform information in a human-readable format.
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
