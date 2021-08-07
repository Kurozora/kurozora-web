<?php

namespace App\Http\Controllers;

use App\Models\AppTheme;
use App\Helpers\JSONResult;
use App\Http\Resources\AppThemeResource;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AppThemeController extends Controller
{
    /**
     * Return an overview of themes.
     *
     * @return JsonResponse
     */
    function overview(): JsonResponse
    {
        $themes = AppTheme::with('media')->get();

        return JSONResult::success([
            'data' => AppThemeResource::collection($themes)
        ]);
    }

    /**
     * Returns the information for a theme.
     *
     * @param AppTheme $theme
     * @return JsonResponse
     */
    public function details(AppTheme $theme): JsonResponse
    {
        return JSONResult::success([
            'data' => AppThemeResource::collection([$theme])
        ]);
    }

    /**
     * Serves the plist file to be downloaded
     *
     * @param AppTheme $theme
     * @return Response
     * @throws AuthorizationException
     */
    function download(AppTheme $theme): Response
    {
        // Get the auth user
        $user = Auth::user();

        if (empty($user->receipt) || !$user->receipt->is_subscribed ?? true) {
            throw new AuthorizationException('Premium themes are only available to pro users.');
        }

        // Increment the download count of the theme
        $theme->update([
            'download_count' => $theme->download_count + 1
        ]);

        // Return the file
        return $theme->download();
    }
}
