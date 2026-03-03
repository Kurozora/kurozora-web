<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Model;
use Random\RandomException;

trait HasPublicID
{
    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Bootstrap the model with a public ID.
     *
     * @return void
     * @throws RandomException
     */
    protected static function bootHasPublicID(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->public_id)) {
                $attempts = 0;

                do {
                    $model->public_id = self::generatePublicID();
                    $attempts++;
                } while ($attempts < 5 && static::where('public_id', $model->public_id)->exists());
            }
        });
    }

    /**
     * Generate a unique public ID for the episode.
     *
     * The ID is a URL-safe base64 string of 16 characters (12 bytes) that is generated using random bytes.
     * This method makes sure the generated ID is URL-safe by replacing '+' with '-' and '/' with '_', and removing any
     * trailing '=' characters.
     *
     * @throws RandomException
     */
    protected static function generatePublicID(): string
    {
        return rtrim(
            strtr(base64_encode(random_bytes(12)), '+/', '-_'),
            '='
        );
    }
}
