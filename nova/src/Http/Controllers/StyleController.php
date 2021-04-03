<?php

namespace Laravel\Nova\Http\Controllers;

use DateTime;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class StyleController extends Controller
{
    /**
     * Serve the requested stylesheet.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show(NovaRequest $request)
    {
        $path = Arr::get(Nova::allStyles(), $request->style);

        abort_if(is_null($path), 404);

        return response(
            file_get_contents($path),
            200,
            [
                'Content-Type' => 'text/css',
            ]
        )->setLastModified(DateTime::createFromFormat('U', (string) filemtime($path)));
    }
}
