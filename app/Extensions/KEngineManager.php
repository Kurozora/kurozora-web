<?php

namespace App\Extensions;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Laravel\Scout\EngineManager;
use Meilisearch\Client as Meilisearch;

class KEngineManager extends EngineManager
{
    /**
     * Create a Meilisearch engine instance.
     *
     * @return KMeilisearchEngine
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function createMeilisearchDriver(): KMeilisearchEngine
    {
        $this->ensureMeilisearchClientIsInstalled();

        return new KMeilisearchEngine(
            $this->container->make(Meilisearch::class),
            config('scout.soft_delete', false)
        );
    }
}
