<?php

namespace App;

use App\Rules\ValidatePlatformName;
use App\Rules\ValidatePlatformVersion;
use App\Rules\ValidateVendorName;

class Session extends KModel
{
    // Table name
    const TABLE_NAME = 'sessions';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
        'last_validated_at'
    ];

    /**
     * Returns the user that owns the session.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Returns whether or not the session is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Returns the platform information in a human readable format.
     *
     * @return string
     */
    function humanReadablePlatform()
    {
        if($this->device_model == null ||
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
    static function platformRules()
    {
        return [
            'platform'          => ['bail', 'required', new ValidatePlatformName],
            'platform_version'  => ['bail', 'required', new ValidatePlatformVersion],
            'device_vendor'     => ['bail', 'required', new ValidateVendorName],
            'device_model'      => ['bail', 'required', 'string', 'max:50']
        ];
    }
}
