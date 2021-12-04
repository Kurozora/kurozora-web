<?php

namespace App\Extensions;

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
    public function destroy($sessionId): bool
    {
        Session::firstWhere('id', $sessionId)?->delete();

        return true;
    }
}
