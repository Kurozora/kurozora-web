<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lukeraymonddowning\Honey\Models\Spammer;
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
        $privacyPolicyMarkdown = $this->getContentOfFile('privacy_policy.md');

        // Prepare for converting Markdown to HTML
        $privacyPolicyText = Markdown::parse($privacyPolicyMarkdown);

        return JSONResult::success([
            'data' => [
                'type'          => 'legal',
                'href'          => route('api.legal.privacy-policy', [], false),
                'attributes'    => [
                    'text' => $privacyPolicyText->toHtml(),
                ],
            ],
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
        $termsOfUseMarkdown = $this->getContentOfFile('terms_of_use.md');

        // Prepare for converting Markdown to HTML
        $termsOfUseText = Markdown::parse($termsOfUseMarkdown);

        return JSONResult::success([
            'data' => [
                'type'          => 'legal',
                'href'          => route('api.legal.terms-of-use', [], false),
                'attributes'    => [
                    'text' => $termsOfUseText->toHtml(),
                ],
            ],
        ]);
    }

    /**
     * Returns the Apple-App-Site-Association JSON.
     *
     * @return mixed
     *
     * @throws FileNotFoundException
     */
    public function appleAppSiteAssociation(): mixed
    {
        // Get the resource path
        $resourcePath = resource_path('docs/apple-app-site-association.json');

        // Get the file content
        $fileContent = File::get($resourcePath);

        // Return the file as JSON
        return json_decode($fileContent);
    }

    /**
     * Get the content of a file with the given name.
     *
     * @param string $fileName
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function getContentOfFile(string $fileName): string
    {
        $filePath = resource_path('docs/' . $fileName);

        // Get the last update date
        $lastUpdateUnix = Carbon::createFromTimestamp(File::lastModified($filePath));
        $lastUpdateStr = $lastUpdateUnix->format('F d, Y');

        // Attach date and return file content
        return str_replace('#UPDATE_DATE#', $lastUpdateStr, File::get($filePath));
    }

    /**
     * Mark a request as spammer.
     *
     * @param Request $request
     */
    protected function markSpammer(Request $request)
    {
        Spammer::markAttempt($request->ip());
    }
}
