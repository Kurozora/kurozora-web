<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\JsonResponse;

class LanguageController extends Controller
{
    /**
     * Generate an overview of languages.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all languages
        $languages = Language::get();

        // Show languages in response
        return JSONResult::success([
            'data' => LanguageResource::collection($languages)
        ]);
    }

    /**
     * Shows language details
     *
     * @param Language $language
     * @return JsonResponse
     */
    public function details(Language $language): JsonResponse
    {
        // Show language details
        return JSONResult::success([
            'data' => LanguageResource::collection([$language])
        ]);
    }
}
