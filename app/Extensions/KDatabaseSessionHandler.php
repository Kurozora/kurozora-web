<?php

namespace App\Extensions;

use App\Http\Middleware\CacheableGuestResponse;
use App\Models\Session;
use Illuminate\Session\DatabaseSessionHandler as BaseDatabaseSessionHandler;

class KDatabaseSessionHandler extends BaseDatabaseSessionHandler
{
    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function write($sessionId, $data): bool
    {
        $attributes = request()?->attributes;

        if ($attributes?->get('bot', false) === true || $attributes?->get(CacheableGuestResponse::ATTRIBUTE, false) === true) {
            return true;
        }

        return parent::write($sessionId, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function destroy($sessionId): bool
    {
        Session::firstWhere('id', $sessionId)?->delete();

        return true;
    }
}
