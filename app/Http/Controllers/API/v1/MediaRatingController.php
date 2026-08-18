<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ReviewKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetRatingCategoriesRequest;
use App\Http\Requests\GetUserReviewsRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\PersonResourceIdentity;
use App\Http\Resources\RatingCategoryResource;
use App\Http\Resources\SongResourceIdentity;
use App\Http\Resources\StudioResourceIdentity;
use App\Models\Episode;
use App\Models\MediaRating;
use App\Models\RatingCategory;
use App\Models\User;
use App\Traits\Controller\WithStateVersionETag;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class MediaRatingController extends Controller
{
    use WithStateVersionETag;

    /**
     * Shows song details.
     *
     * @param MediaRating $mediaRating
     * @return JsonResponse
     */
    public function details(MediaRating $mediaRating): JsonResponse
    {
        $mediaRating->load([
            'user' => function ($query) {
                $query->withProfileEagerLoad(auth()->user());
            },
        ]);

        if ($mediaRating->model_type === Episode::class) {
            $mediaRating->episode_public_id = Episode::withoutGlobalScopes()
                ->whereKey($mediaRating->model_id)
                ->value('public_id');
        }

        return JSONResult::success([
            'data' => MediaRatingResource::collection([$mediaRating])
        ]);
    }

    /**
     * Soft-deletes the given media rating.
     *
     * @param MediaRating $mediaRating
     *
     * @return JsonResponse
     */
    public function delete(MediaRating $mediaRating)
    {
        $mediaRating->delete();

        return JSONResult::success();
    }

    /**
     * Returns the user's review-state Resources for the requested model IDs.
     *
     * @throws AuthorizationException
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function overlay(GetUserReviewsRequest $request, User $user): JsonResponse
    {
        if (auth()->id() !== $user->id) {
            throw new AuthorizationException(__('Reviews state is currently visible only to its owner.'));
        }

        $data = $request->validated();
        $reviewKind = ReviewKind::fromValue((int) $data['kind']);
        $ids = array_values(array_unique($data['ids']));
        sort($ids);

        $fingerprint = [
            'kind' => $reviewKind->value,
            'ids' => $ids,
        ];
        $notModified = $this->returnIfNotModified($request, $user, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }
        $etag = $this->stateVersionETag($user, $fingerprint);

        $morphClass = $reviewKind->getMorphClass();
        $relationshipKey = match ($reviewKind->value) {
            ReviewKind::Manga => 'literatures',
            ReviewKind::Game => 'games',
            ReviewKind::Character => 'characters',
            ReviewKind::Person => 'people',
            ReviewKind::Studio => 'studios',
            ReviewKind::Song => 'songs',
            ReviewKind::Episode => 'episodes',
            default => 'shows',
        };
        $identityClass = match ($reviewKind->value) {
            ReviewKind::Manga => LiteratureResourceIdentity::class,
            ReviewKind::Game => GameResourceIdentity::class,
            ReviewKind::Character => CharacterResourceIdentity::class,
            ReviewKind::Person => PersonResourceIdentity::class,
            ReviewKind::Studio => StudioResourceIdentity::class,
            ReviewKind::Song => SongResourceIdentity::class,
            ReviewKind::Episode => EpisodeResourceIdentity::class,
            default => AnimeResourceIdentity::class,
        };

        // Episodes use public_id in their identity refs; resolve numeric → public_id.
        // Bypass the TV-rating scope; the user already engaged with these episodes.
        $episodePublicIds = [];
        if ($reviewKind->value === ReviewKind::Episode) {
            $reviewedIDs = MediaRating::where('user_id', '=', $user->id)
                ->where('model_type', '=', $morphClass)
                ->whereIn('model_id', $ids)
                ->select('model_id');
            $episodePublicIds = Episode::withoutGlobalScopes()
                ->whereIn('id', $reviewedIDs)
                ->pluck('public_id', 'id')
                ->all();
        }

        $entries = [];

        MediaRating::where('user_id', '=', $user->id)
            ->where('model_type', '=', $morphClass)
            ->whereIn('model_id', $ids)
            ->select(['model_id', 'rating', 'description', 'note', 'is_spoiler', 'recommendation', 'created_at', 'updated_at'])
            ->cursor()
            ->each(function ($row) use (&$entries, $relationshipKey, $identityClass, $reviewKind, $episodePublicIds) {
                $identityValue = $reviewKind->value === ReviewKind::Episode
                    ? ($episodePublicIds[$row->model_id] ?? null)
                    : $row->model_id;

                if ($identityValue === null) {
                    return;
                }

                $entries[] = [
                    'attributes' => [
                        'score' => (float) $row->rating,
                        'description' => $row->description,
                        'note' => $row->note,
                        'isSpoiler' => (bool) $row->is_spoiler,
                        'recommendation' => $row->recommendation?->value,
                        'createdAt' => $row->created_at ? Carbon::parse($row->created_at)->timestamp : null,
                        'updatedAt' => $row->updated_at ? Carbon::parse($row->updated_at)->timestamp : null,
                    ],
                    'relationships' => [
                        $relationshipKey => [
                            'data' => $identityClass::collection([$identityValue]),
                        ],
                    ],
                ];
            });

        return JSONResult::success([
            'data' => $entries,
        ])->withHeaders($this->stateVersionHeaders($etag, $user));
    }

    /**
     * Returns the rating categories of the requested kind.
     *
     * @param GetRatingCategoriesRequest $request
     *
     * @return JsonResponse
     * @throws InvalidEnumMemberException
     */
    public function categories(GetRatingCategoriesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $reviewKind = ReviewKind::fromValue((int) $data['kind']);
        $morphClass = $reviewKind->getMorphClass();

        $ratingCategories = RatingCategory::forModelType($morphClass)
            ->get();

        $this->attachUserScores($ratingCategories, $morphClass, $data['id'] ?? null);

        return JSONResult::success([
            'data' => RatingCategoryResource::collection($ratingCategories),
        ]);
    }

    /**
     * Attaches the authenticated user's score and review to each rating category.
     *
     * @param Collection  $ratingCategories
     * @param string      $morphClass
     * @param null|string $modelID
     *
     * @return void
     */
    private function attachUserScores(Collection $ratingCategories, string $morphClass, ?string $modelID): void
    {
        $ratingCategories->each(function (RatingCategory $ratingCategory) {
            $ratingCategory->user_score = null;
            $ratingCategory->user_review = null;
        });

        $user = auth()->user();

        if ($user === null || $modelID === null || $ratingCategories->isEmpty()) {
            return;
        }

        $modelKey = $morphClass === Episode::class
            ? Episode::withoutGlobalScopes()
                ->where('public_id', '=', $modelID)
                ->value('id')
            : $modelID;

        if ($modelKey === null) {
            return;
        }

        $mediaRating = $user->mediaRatings()
            ->where('model_type', '=', $morphClass)
            ->where('model_id', '=', $modelKey)
            ->with('categoryScores')
            ->first();

        if ($mediaRating === null) {
            return;
        }

        $categoryScores = $mediaRating->categoryScores
            ->keyBy('rating_category_id');

        $ratingCategories->each(function (RatingCategory $ratingCategory) use ($categoryScores) {
            $categoryScore = $categoryScores->get($ratingCategory->id);
            $ratingCategory->user_score = $categoryScore?->score;
            $ratingCategory->user_review = $categoryScore?->review;
        });
    }
}
