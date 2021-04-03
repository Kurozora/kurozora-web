<?php

namespace Laravel\Nova;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Query\Builder;

class GlobalSearch
{
    /**
     * The request instance.
     *
     * @var \Laravel\Nova\Http\Requests\NovaRequest
     */
    public $request;

    /**
     * The resource class names that should be searched.
     *
     * @var array
     */
    public $resources;

    /**
     * Create a new global search instance.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $resources
     * @return void
     */
    public function __construct(NovaRequest $request, $resources)
    {
        $this->request = $request;
        $this->resources = $resources;
    }

    /**
     * Get the matching resources.
     *
     * @return array
     */
    public function get()
    {
        return iterator_to_array($this->getSearchResults(), false);
    }

    /**
     * Get the search results for the resources.
     *
     * @return \Generator
     */
    protected function getSearchResults()
    {
        foreach ($this->resources as $resourceClass) {
            $query = (new Builder($resourceClass))->search(
                $this->request, $resourceClass::newModel()->newQuery(),
                $this->request->search
            );

            yield from $query->limit($resourceClass::$globalSearchResults)
                ->cursor()
                ->mapInto($resourceClass)
                ->map(function ($resource) use ($resourceClass) {
                    return $this->transformResult($resourceClass, $resource);
                });
        }
    }

    /**
     * Transform the result from resource.
     *
     * @param  string  $resourceClass
     * @param  \Laravel\Nova\Resource  $resource
     * @return array
     */
    protected function transformResult($resourceClass, Resource $resource)
    {
        $model = $resource->model();

        return [
            'resourceName' => $resourceClass::uriKey(),
            'resourceTitle' => $resourceClass::label(),
            'title' => (string) $resource->title(),
            'subTitle' => transform($resource->subtitle(), function ($subtitle) {
                return (string) $subtitle;
            }),
            'resourceId' => $model->getKey(),
            'url' => url(Nova::path().'/resources/'.$resourceClass::uriKey().'/'.$model->getKey()),
            'avatar' => $resource->resolveAvatarUrl($this->request),
            'rounded' => $resource->resolveIfAvatarShouldBeRounded($this->request),
            'linksTo' => $resource->globalSearchLink($this->request),
        ];
    }
}
