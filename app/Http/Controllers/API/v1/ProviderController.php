<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use App\Scopes\PublicScope;
use Illuminate\Http\JsonResponse;

class ProviderController extends Controller
{
    /**
     * Returns the providers index.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $providers = Provider::withoutGlobalScope(PublicScope::class)
            ->with('media')
            ->orderBy('original_name')
            ->get();

        return JSONResult::success([
            'data' => ProviderResource::collection($providers)
        ]);
    }
}
