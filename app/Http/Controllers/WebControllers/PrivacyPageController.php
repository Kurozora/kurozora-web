<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class PrivacyPageController extends Controller
{
    /**
     * Shows the privacy page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function show() {
        $request = Request::create('/api/v1/privacy-policy', 'GET');

        $responseData = (array) Route::dispatch($request)->getData();

        return view('website.privacy', [
            'policyText'    => nl2br($responseData['privacy_policy']->text),
            'lastUpdated'   => $responseData['privacy_policy']->last_update
        ]);
    }
}
