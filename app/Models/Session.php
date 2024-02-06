<?php

namespace App\Models;

use App\Rules\ValidatePlatformName;
use App\Rules\ValidatePlatformVersion;
use App\Traits\Model\HasSessionAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Session extends KModel
{
    use HasFactory,
        HasSessionAttribute;

    // Table name
    const string TABLE_NAME = 'sessions';
    protected $table = self::TABLE_NAME;

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
     * Returns the user that owns the session.
     *
     * @return BelongsTo
     */
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The session attribute of the personal access token.
     *
     * @return morphOne
     */
    public function session_attribute(): MorphOne
    {
        return $this->morphOne(SessionAttribute::class, 'model', localKey: 'id');
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
            'device_vendor'     => ['bail', 'required', 'string', 'min:1'],
            'device_model'      => ['bail', 'required', 'string', 'max:50']
        ];
    }
}
