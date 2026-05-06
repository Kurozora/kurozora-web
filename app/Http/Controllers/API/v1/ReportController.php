<?php

namespace App\Http\Controllers\API\v1;

use App\Contracts\Reportable;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * Persist a polymorphic report against the given reportable model.
     *
     * @param ReportRequest $request
     * @param Reportable    $reportable
     *
     * @return JsonResponse
     */
    public function store(ReportRequest $request, Reportable $reportable): JsonResponse
    {
        /** @var Model $model */
        $model = $reportable;
        $data = $request->validated();
        $user = auth()->user();

        Report::create([
            'reportable_type' => $model->getMorphClass(),
            'reportable_id' => $model->getKey(),
            'user_id' => $user->id,
            'reason_key' => $data['reason_key'],
            'details' => $data['details'] ?? null,
        ]);

        return JSONResult::success();
    }
}
