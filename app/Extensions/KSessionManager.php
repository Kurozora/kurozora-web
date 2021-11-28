<?php

namespace App\Extensions;

use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;

class KSessionManager extends SessionManager
{
    /**
     * Create an instance of the database session driver.
     *
     * @return Store
     */
    protected function createDatabaseDriver(): Store
    {
        $table = $this->config->get('session.table');

        $lifetime = $this->config->get('session.lifetime');

        return $this->buildSession(new KDatabaseSessionHandler(
            $this->getDatabaseConnection(), $table, $lifetime, $this->container
        ));
    }
}
