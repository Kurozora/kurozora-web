<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PrivacyPageController extends Controller
{
    /**
     * Shows the privacy page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function show() {
        $privacyPolicyRequest = Request::create('/api/v1/privacy-policy', 'GET');
        $responseData = (array) Route::dispatch($privacyPolicyRequest)->getData();

        return view('website.legal.privacy', [
            'policyText'    => $responseData['privacy_policy']->text
        ]);
    }
}
