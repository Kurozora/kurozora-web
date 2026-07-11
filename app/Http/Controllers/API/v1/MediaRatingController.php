<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ReviewKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetUserReviewsRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\PersonResourceIdentity;
use App\Http\Resources\SongResourceIdentity;
use App\Http\Resources\StudioResourceIdentity;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use App\Traits\Controller\WithStateVersionETag;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
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

        $morphClass = match ($reviewKind->value) {
            ReviewKind::Manga => Manga::class,
            ReviewKind::Game => Game::class,
            ReviewKind::Character => Character::class,
            ReviewKind::Person => Person::class,
            ReviewKind::Studio => Studio::class,
            ReviewKind::Song => Song::class,
            ReviewKind::Episode => Episode::class,
            default => Anime::class,
        };
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
            ->select(['model_id', 'rating', 'description', 'created_at', 'updated_at'])
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
}
