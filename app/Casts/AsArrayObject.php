<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject as BaseAsArrayObject;

class AsArrayObject extends BaseAsArrayObject
{
    /**
     * @inheritDoc
     */
    public static function castUsing(array $arguments): object|string
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes): ?ArrayObject
            {
                if (isset($attributes[$key])) {
                    return $attributes[$key] === 'null' ? null : new ArrayObject(json_decode($attributes[$key], true));
                }
                return null;
            }

            public function set($model, $key, $value, $attributes): array
            {
                $value = $value === 'null' || is_null($value) ? null : json_encode($value);
                return [$key => $value];
            }

            public function serialize($model, string $key, $value, array $attributes)
            {
                return $value->getArrayCopy();
            }
        };
    }
}
