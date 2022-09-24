<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Resources\AppThemeResource;
use App\Models\AppTheme;
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
    function index(): JsonResponse
    {
        $appThemes = AppTheme::with('media')->get();

        return JSONResult::success([
            'data' => AppThemeResource::collection($appThemes)
        ]);
    }

    /**
     * Returns the information for a theme.
     *
     * @param AppTheme $appTheme
     * @return JsonResponse
     */
    public function details(AppTheme $appTheme): JsonResponse
    {
        return JSONResult::success([
            'data' => AppThemeResource::collection([$appTheme])
        ]);
    }

    /**
     * Serves the plist file to be downloaded
     *
     * @param AppTheme $appTheme
     * @return Response
     * @throws AuthorizationException
     */
    function download(AppTheme $appTheme): Response
    {
        // Get the auth user
//        $user = auth()->user();
//
//        if (empty($user->receipt) || !$user->receipt->is_subscribed ?? true) {
//            throw new AuthorizationException('Premium platform themes are only available to pro users.');
//        }

        // Increment the download count of the theme
        $appTheme->update([
            'download_count' => $appTheme->download_count + 1
        ]);

        // Return the file
        return $appTheme->download();
    }
}
