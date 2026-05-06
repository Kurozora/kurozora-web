<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ParentalGuideRating;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParentalGuideEntryRequest;
use App\Http\Resources\ParentalGuideEntryResource;
use App\Http\Resources\ParentalGuideStatsResource;
use App\Jobs\UpdateParentalGuideStatsJob;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\ParentalGuideEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentalGuideController extends Controller
{
    /**
     * Get the parental guide aggregate + entries for a show.
     *
     * @param Request $request
     * @param Anime   $anime
     *
     * @return JsonResponse
     */
    public function indexForAnime(Request $request, Anime $anime): JsonResponse
    {
        return $this->index($request, $anime);
    }

    /**
     * Get the parental guide aggregate + entries for a literature.
     *
     * @param Request $request
     * @param Manga   $manga
     *
     * @return JsonResponse
     */
    public function indexForManga(Request $request, Manga $manga): JsonResponse
    {
        return $this->index($request, $manga);
    }

    /**
     * Get the parental guide aggregate + entries for a game.
     *
     * @param Request $request
     * @param Game    $game
     *
     * @return JsonResponse
     */
    public function indexForGame(Request $request, Game $game): JsonResponse
    {
        return $this->index($request, $game);
    }

    /**
     * Submit (or upsert) a parental guide entry for a show.
     *
     * @param StoreParentalGuideEntryRequest $request
     * @param Anime                          $anime
     *
     * @return JsonResponse
     */
    public function storeForAnime(StoreParentalGuideEntryRequest $request, Anime $anime): JsonResponse
    {
        return $this->store($request, $anime);
    }

    /**
     * Submit (or upsert) a parental guide entry for a literature.
     *
     * @param StoreParentalGuideEntryRequest $request
     * @param Manga                          $manga
     *
     * @return JsonResponse
     */
    public function storeForManga(StoreParentalGuideEntryRequest $request, Manga $manga): JsonResponse
    {
        return $this->store($request, $manga);
    }

    /**
     * Submit (or upsert) a parental guide entry for a game.
     *
     * @param StoreParentalGuideEntryRequest $request
     * @param Game                           $game
     *
     * @return JsonResponse
     */
    public function storeForGame(StoreParentalGuideEntryRequest $request, Game $game): JsonResponse
    {
        return $this->store($request, $game);
    }

    /**
     * Read implementation shared between anime / manga / game.
     *
     * @param Request $request
     * @param Model   $model
     *
     * @return JsonResponse
     */
    private function index(Request $request, Model $model): JsonResponse
    {
        $authUser = auth()->user();

        $entries = ParentalGuideEntry::query()
            ->visible()
            ->withReason()
            ->where('model_type', '=', $model->getMorphClass())
            ->where('model_id', '=', $model->id)
            ->with(ParentalGuideEntry::lockupEagerLoads($authUser))
            ->orderByDesc('created_at')
            ->get();

        $model->loadMissing('parental_guide_stat');

        return JSONResult::success([
            'data' => [
                'stats' => ParentalGuideStatsResource::make($model->parental_guide_stat),
                'entries' => ParentalGuideEntryResource::collection($entries),
            ],
        ]);
    }

    /**
     * Create a new parental guide entry.
     *
     * @param StoreParentalGuideEntryRequest $request
     * @param Model                          $model
     *
     * @return JsonResponse
     */
    private function store(StoreParentalGuideEntryRequest $request, Model $model): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();
        $hasSeverity = (int) $data['rating'] !== ParentalGuideRating::None;

        $entry = ParentalGuideEntry::create([
            'user_id' => $user->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->id,
            'category' => (int) $data['category'],
            'rating' => (int) $data['rating'],
            'frequency' => $hasSeverity && isset($data['frequency']) ? (int) $data['frequency'] : null,
            'depiction' => $hasSeverity && isset($data['depiction']) ? (int) $data['depiction'] : null,
            'reason' => $data['reason'] ?? null,
            'is_spoiler' => (bool) $data['is_spoiler'],
        ]);

        UpdateParentalGuideStatsJob::dispatch($model->getMorphClass(), $model->id);

        $entry->load(ParentalGuideEntry::lockupEagerLoads($user));

        return JSONResult::success([
            'data' => ParentalGuideEntryResource::collection([$entry]),
        ]);
    }
}
