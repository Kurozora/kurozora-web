<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Contracts\RelatableField;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class MorphableController extends Controller
{
    /**
     * List the available morphable resources for a given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function __invoke(NovaRequest $request)
    {
        $relatedResource = Nova::resourceForKey($request->type);

        abort_if(is_null($relatedResource), 403);

        $field = $request->newResource()
                        ->availableFieldsOnIndexOrDetail($request)
                        ->whereInstanceOf(RelatableField::class)
                        ->findFieldByAttribute($request->field, function () {
                            abort(404);
                        });

        $withTrashed = $this->shouldIncludeTrashed(
            $request, $relatedResource
        );

        $limit = $relatedResource::usesScout()
                    ? $relatedResource::$scoutSearchResults
                    : $relatedResource::$relatableSearchResults;

        return [
            'resources' => $field->buildMorphableQuery($request, $relatedResource, $withTrashed)
                                ->take($limit)
                                ->get()
                                ->mapInto($relatedResource)
                                ->filter->authorizedToAdd($request, $request->model())
                                ->map(function ($resource) use ($request, $field, $relatedResource) {
                                    return $field->formatMorphableResource($request, $resource, $relatedResource);
                                })->sortBy('display')->values(),
            'withTrashed' => $withTrashed,
            'softDeletes' => $relatedResource::softDeletes(),
        ];
    }

    /**
     * Determine if the query should include trashed models.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $associatedResource
     * @return bool
     */
    protected function shouldIncludeTrashed(NovaRequest $request, $associatedResource)
    {
        if ($request->withTrashed === 'true') {
            return true;
        }

        $associatedModel = $associatedResource::newModel();

        if ($request->current && empty($request->search) && $associatedResource::softDeletes()) {
            $associatedModel = $associatedModel->newQueryWithoutScopes()->find($request->current);

            return $associatedModel ? $associatedModel->trashed() : false;
        }

        return false;
    }
}
