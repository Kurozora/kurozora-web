<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideRating;
use App\Enums\ParentalGuideReaction;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Http\Requests\StoreParentalGuideEntryRequest;
use App\Http\Requests\VoteParentalGuideEntryRequest;
use App\Http\Resources\ParentalGuideEntryResource;
use App\Jobs\UpdateParentalGuideStatsJob;
use App\Models\ParentalGuideEntry;
use App\Models\Report;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class ParentalGuideEntryController extends Controller
{
    /**
     * Delete the given parental guide entry.
     *
     * @param ParentalGuideEntry $parentalGuideEntry
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(ParentalGuideEntry $parentalGuideEntry): JsonResponse
    {
        $this->authorize('delete', $parentalGuideEntry);

        $modelType = $parentalGuideEntry->model_type;
        $modelID = $parentalGuideEntry->model_id;

        $parentalGuideEntry->delete();

        UpdateParentalGuideStatsJob::dispatch($modelType, $modelID);

        return JSONResult::success();
    }

    /**
     * Update the given parental guide entry in place.
     *
     * @param StoreParentalGuideEntryRequest $request
     * @param ParentalGuideEntry             $parentalGuideEntry
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function update(StoreParentalGuideEntryRequest $request, ParentalGuideEntry $parentalGuideEntry): JsonResponse
    {
        $this->authorize('update', $parentalGuideEntry);

        $data = $request->validated();
        $hasSeverity = (int) $data['rating'] !== ParentalGuideRating::None;
        $supportsDepiction = $hasSeverity && match ((int) $data['category']) {
            ParentalGuideCategory::SexAndNudity,
            ParentalGuideCategory::ViolenceAndGore,
            ParentalGuideCategory::FrighteningAndIntenseScenes => true,
            default => false,
        };
        $depiction = $supportsDepiction && isset($data['depiction']) ? (int) $data['depiction'] : null;

        $parentalGuideEntry->update([
            'category' => (int) $data['category'],
            'rating' => (int) $data['rating'],
            'frequency' => $hasSeverity && isset($data['frequency']) ? (int) $data['frequency'] : null,
            'depiction' => $depiction,
            'reason' => $data['reason'] ?? null,
            'is_spoiler' => (bool) $data['is_spoiler'],
        ]);

        UpdateParentalGuideStatsJob::dispatch($parentalGuideEntry->model_type, $parentalGuideEntry->model_id);

        $user = auth()->user();
        $parentalGuideEntry->load(ParentalGuideEntry::lockupEagerLoads($user));

        return JSONResult::success([
            'data' => ParentalGuideEntryResource::collection([$parentalGuideEntry]),
        ]);
    }

    /**
     * Toggle the (un)helpful vote on the entry.
     *
     * @param VoteParentalGuideEntryRequest $request
     * @param ParentalGuideEntry            $parentalGuideEntry
     *
     * @return JsonResponse
     */
    public function vote(VoteParentalGuideEntryRequest $request, ParentalGuideEntry $parentalGuideEntry): JsonResponse
    {
        $user = auth()->user();
        $voteString = $request->validated()['vote'] ?? null;
        $reaction = match ($voteString) {
            'helpful' => ParentalGuideReaction::Helpful(),
            'unhelpful' => ParentalGuideReaction::Unhelpful(),
            default => null,
        };

        $user->setHelpfulness($parentalGuideEntry, $reaction);

        $parentalGuideEntry->load(ParentalGuideEntry::lockupEagerLoads($user));
        $newReaction = $user->getHelpfulnessFor($parentalGuideEntry);
        $isHelpful = $newReaction?->is(ParentalGuideReaction::Helpful());

        return JSONResult::success([
            'data' => [
                'isHelpful' => $isHelpful,
            ],
        ]);
    }

    /**
     * File a report against the entry.
     *
     * @param ReportRequest      $request
     * @param ParentalGuideEntry $parentalGuideEntry
     *
     * @return JsonResponse
     */
    public function report(ReportRequest $request, ParentalGuideEntry $parentalGuideEntry): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        Report::create([
            'reportable_type' => $parentalGuideEntry->getMorphClass(),
            'reportable_id' => $parentalGuideEntry->getKey(),
            'user_id' => $user->id,
            'reason_key' => $data['reason_key'],
            'details' => $data['details'] ?? null,
        ]);

        return JSONResult::success();
    }
}
