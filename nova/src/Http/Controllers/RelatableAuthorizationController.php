<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class RelatableAuthorizationController extends Controller
{
    /**
     * Get the relatable authorization status for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function __invoke(NovaRequest $request)
    {
        $parentResource = $request->findParentResourceOrFail();
        $resource = $request->resource();

        if ($request->viaManyToMany()) {
            return ['authorized' => $parentResource->authorizedToAttachAny(
                $request, $request->model()
            )];
        }

        return ['authorized' => $parentResource->authorizedToAdd(
            $request, $request->model()
        ) && $resource::authorizedToCreate($request)];
    }
}
