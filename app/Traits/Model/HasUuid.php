<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Bootstrap the model with UUID.
     *
     * @return void
     */
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Find model by UUID.
     *
     * @param string $uuid
     * @return Model|null
     */
    public static function findByUuid(string $uuid): ?Model
    {
        return static::where('uuid', $uuid)->first();
    }
}
