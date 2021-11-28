<?php

namespace App\Models;

use App\Rules\ValidatePlatformName;
use App\Rules\ValidatePlatformVersion;
use App\Rules\ValidateVendorName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    static function boot()
    {
        parent::boot();

        static::deleting(function($session) {
            $session->session_attribute()->delete();
        });
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
     * The session attribute of the session.
     *
     * @return MorphOne
     */
    function session_attribute(): MorphOne
    {
        return $this->morphOne(SessionAttribute::class, 'model')->where('model_type', Session::class);
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
