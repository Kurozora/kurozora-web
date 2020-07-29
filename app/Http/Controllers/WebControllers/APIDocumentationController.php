<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;

class APIDocumentationController extends Controller
{
    /**
     * Renders the API documentation page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function render() {
        return view('website.api', [
            'openapi_json_file' => asset('openapi.json'),
        ]);
    }
}
