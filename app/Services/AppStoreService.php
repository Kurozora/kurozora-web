<?php

namespace App\Services;

use AppStoreServerLibrary\AppStoreServerAPIClient;
use AppStoreServerLibrary\Models\Environment;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class AppStoreService
{
    /**
     * Get an instance of the App Store Server API client.
     *
     * @throws ServiceUnavailableHttpException
     */
    public function client(?string $env = null): AppStoreServerAPIClient
    {
        $config = config('services.apple.store_kit');

        $missing = array_filter(
            ['private_key', 'key_id', 'issuer_id', 'bundle_id'],
            fn (string $key): bool => empty($config[$key])
        );

        if ($missing) {
            logger()->error('App Store Server API client is not configured.', ['missing' => array_values($missing)]);

            throw new ServiceUnavailableHttpException(null, 'Apple verification service unavailable.');
        }

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
