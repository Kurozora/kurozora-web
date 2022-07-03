<?php

namespace App\Extensions;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Laravel\Scout\EngineManager;
use MeiliSearch\Client as MeiliSearch;

class KEngineManager extends EngineManager
{
    /**
     * Create an MeiliSearch engine instance.
     *
     * @return KMeiliSearchEngine
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function createMeilisearchDriver(): KMeiliSearchEngine
    {
        $this->ensureMeiliSearchClientIsInstalled();

        return new KMeiliSearchEngine(
            $this->container->make(MeiliSearch::class),
            config('scout.soft_delete', false)
        );
    }
}
