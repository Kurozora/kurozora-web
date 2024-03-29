<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\AppThemeDownloadKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\DownloadAppThemeRequest;
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
        $appThemes = AppTheme::with(['media'])
            ->get();

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
        $appTheme->load(['media']);

        return JSONResult::success([
            'data' => AppThemeResource::collection([$appTheme])
        ]);
    }

    /**
     * Serves the plist file to be downloaded
     *
     * @param DownloadAppThemeRequest $request
     * @param AppTheme $appTheme
     * @return Response
     */
    function download(DownloadAppThemeRequest $request, AppTheme $appTheme): Response
    {
        // Get the auth user
        $user = auth()?->user();

        if (!($user?->is_subscribed || $user?->is_pro)) {
            throw new AuthorizationException(__('Premium platform themes are only available to pro and subscribed users.'));
        }

        // Get download type
        $data = $request->validated();
        $downloadKind = AppThemeDownloadKind::fromValue($data['type'] ?? 0);

        // Increment the download count of the theme
        $appTheme->update([
            'download_count' => $appTheme->download_count + 1
        ]);

        // Return the file
        return $appTheme->download($downloadKind);
    }
}
