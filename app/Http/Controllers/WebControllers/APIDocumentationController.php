<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class APIDocumentationController extends Controller
{
    /**
     * Renders the API documentation page.
     *
     * @return Application|Factory|View
     */
    function render() {
        return view('website.api', [
            'openapi_json_file' => asset('openapi.json'),
        ]);
    }
}
