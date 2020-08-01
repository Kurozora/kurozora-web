<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class PrivacyPageController extends Controller
{
    /**
     * Shows the privacy page.
     *
     * @return Application|Factory|View
     */
    function show() {
        $privacyPolicyRequest = Request::create('/api/v1/privacy-policy', 'GET');
        $responseData = (array) Route::dispatch($privacyPolicyRequest)->getData();

        return view('website.legal.privacy', [
            'policyText'    => $responseData['data']->privacy_policy
        ]);
    }
}
