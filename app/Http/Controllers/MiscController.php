<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MiscController extends Controller
{
    /**
     * Returns the latest privacy policy.
     *
     * @return JsonResponse
     */
    public function getPrivacyPolicy() {
        $privacyPolicyPath = 'public/privacy_policy.txt';

        // Get the privacy policy text
        $privacyPolicyText = null;

        // Create the privacy policy file if it does not exist yet
        if(!Storage::exists($privacyPolicyPath))
            Storage::put($privacyPolicyPath, 'Privacy Policy is empty. Please inform an administrator.');

        // Get the last update date
        $lastUpdateUnix = Storage::lastModified($privacyPolicyPath);
        $lastUpdateStr = date('j M, Y', $lastUpdateUnix) . ' at ';
        $lastUpdateStr .= date('H:i', $lastUpdateUnix);

        return JSONResult::success([
            'privacy_policy' => [
                'text'          => Storage::get($privacyPolicyPath),
                'last_update'   => $lastUpdateStr
            ]
        ]);
    }
}
