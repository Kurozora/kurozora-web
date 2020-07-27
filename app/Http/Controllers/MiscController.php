<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\CommonMarkConverter;

class MiscController extends Controller
{
    /**
     * Returns the latest privacy policy.
     *
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getPrivacyPolicy() {
        $privacyPolicyPath = 'resources/static/privacy_policy.md';

        // Get the privacy policy text
        $privacyPolicyText = null;

        // Create the privacy policy file if it does not exist yet
        if(!Storage::exists($privacyPolicyPath))
            Storage::put($privacyPolicyPath, 'Privacy Policy is empty. Please inform an administrator.');

        // Get the last update date
        $lastUpdateUnix = Carbon::createFromTimestamp(Storage::lastModified($privacyPolicyPath));
        $lastUpdateStr = $lastUpdateUnix->format('F d, Y');

        // Get privacy policy text and attach date
        $privacyPolicyText = str_replace('#UPDATE_DATE#', $lastUpdateStr, Storage::get($privacyPolicyPath));

        // Prepare for converting Markdown to HTML
        $commonMarkConverter = new CommonMarkConverter();

        return JSONResult::success([
            'data' => [
                'privacy_policy' => $commonMarkConverter->convertToHtml($privacyPolicyText)
            ]
        ]);
    }
}
