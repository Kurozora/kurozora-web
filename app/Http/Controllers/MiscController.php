<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Markdown;

class MiscController extends Controller
{
    /**
     * Returns the latest privacy policy.
     *
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function getPrivacyPolicy(): JsonResponse
    {
        // Get MarkDown content
        $privacyPolicyMarkdown = $this->getContentOfFile('resources/static/privacy_policy.md');

        // Prepare for converting Markdown to HTML
        $privacyPolicyText = Markdown::parse($privacyPolicyMarkdown);

        return JSONResult::success([
            'data' => [
                'type'          => 'legal',
                'href'          => route('api.legal.privacy-policy', [], false),
                'attributes'    => [
                    'text' => $privacyPolicyText->toHtml()
                ]
            ]
        ]);
    }

    /**
     * Returns the latest terms of use.
     *
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function getTermsOfUse(): JsonResponse
    {
        // Get MarkDown content
        $termsOfUseMarkdown = $this->getContentOfFile('resources/static/terms_of_use.md');

        // Prepare for converting Markdown to HTML
        $termsOfUseText = Markdown::parse($termsOfUseMarkdown);

        return JSONResult::success([
            'data' => [
                'type'          => 'legal',
                'href'          => route('api.legal.terms-of-use', [], false),
                'attributes'    => [
                    'text' => $termsOfUseText->toHtml()
                ]
            ]
        ]);
    }

    /**
     * Get the content of the given file.
     *
     * @param string $filePath
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function getContentOfFile(string $filePath): string
    {
        // Create the file if it does not exist yet
        if (!Storage::exists($filePath)) {
            Storage::put($filePath, 'Page is empty. Please inform an administrator.');
        }

        // Get the last update date
        $lastUpdateUnix = Carbon::createFromTimestamp(Storage::lastModified($filePath));
        $lastUpdateStr = $lastUpdateUnix->format('F d, Y');

        // Attach date and return file content
        return str_replace('#UPDATE_DATE#', $lastUpdateStr, Storage::get($filePath));
    }
}
