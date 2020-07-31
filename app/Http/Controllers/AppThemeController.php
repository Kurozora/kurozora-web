<?php

namespace App\Http\Controllers;

use App\AppTheme;
use App\Helpers\JSONResult;
use App\Http\Resources\AppThemeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AppThemeController extends Controller
{
    /**
     * Return an overview of themes.
     *
     * @return JsonResponse
     */
    function overview(): JsonResponse
    {
        $themes = AppTheme::all();

        return JSONResult::success([
            'data' => AppThemeResource::collection($themes)
        ]);
    }

    /**
     * Returns the information for a theme.
     *
     * @param Request $request
     * @param AppTheme $theme
     * @return JsonResponse
     */
    public function details(Request $request, AppTheme $theme): JsonResponse
    {
        return JSONResult::success([
            'data' => AppThemeResource::collection([$theme])
        ]);
    }

    /**
     * Serves the plist file to be downloaded
     *
     * @param AppTheme $theme
     * @return \Illuminate\Http\Response
     */
    function download(AppTheme $theme): \Illuminate\Http\Response
    {
        // Name for the theme file
        $fileName = 'theme-' . $theme->id . '.plist';

        $content = $theme->pList();

        // Headers to return for the download
        $headers = [
            'Content-type'          => 'application/x-plist',
            'Content-Disposition'   => sprintf('attachment; filename="%s"', $fileName),
            'Content-Length'        => strlen($content)
        ];

        // Return the file
        return Response::make($content, 200, $headers);
    }
}
