<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Markdown;

class LegalController extends Controller
{
    /**
     * Show the privacy policy.
     *
     * @return Application|Factory|View
     */
    public function privacyPolicy(): Application|Factory|View
    {
        return view('legal.privacy-policy', [
            'privacyPolicy' => $this->document(resource_path('docs/privacy_policy.md')),
        ]);
    }

    /**
     * Show the terms of use.
     *
     * @return Application|Factory|View
     */
    public function termsOfUse(): Application|Factory|View
    {
        return view('legal.terms-of-use', [
            'termsOfUse' => $this->document(resource_path('docs/terms_of_use.md')),
        ]);
    }

    /**
     * Render a Markdown document with its last update date filled in.
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function document(string $filePath): string
    {
        $lastUpdate = Carbon::createFromTimestamp(File::lastModified($filePath))->format('F d, Y');

        return Markdown::parse(str_replace('#UPDATE_DATE#', $lastUpdate, File::get($filePath)));
    }
}
