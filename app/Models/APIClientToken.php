<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class APIClientToken extends KModel
{
    use HasFactory,
        HasUuids,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'api_client_tokens';
    protected $table = self::TABLE_NAME;

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($client) {
            $client->token = Str::random(40);
        });
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds(): array
    {
        return [
            'key_id',
        ];
    }

    /**
     * The user the API token belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}
