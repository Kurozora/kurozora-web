<?php

namespace App\Services;

use AppStoreServerLibrary\AppStoreServerAPIClient;
use AppStoreServerLibrary\Models\Environment;

class AppStoreService
{
    /**
     * Get an instance of the App Store Server API client.
     */
    public function client(?string $env = null): AppStoreServerAPIClient
    {
        $config = config('services.apple.store_kit');

        if ($env) {
            $environment = Environment::from($env);
        } else {
            $environment = app()->isProduction() ? Environment::PRODUCTION : Environment::SANDBOX;
        }

        return new AppStoreServerAPIClient(
            $config['private_key'],
            $config['key_id'],
            $config['issuer_id'],
            $config['bundle_id'],
            $environment
        );
    }

    /**
     * Proxy method calls to the App Store Server API client instance.
     */
    public function __call($method, $parameters)
    {
        return $this->client()->$method(...$parameters);
    }
}
