<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ReviewKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetUserNotesRequest;
use App\Http\Requests\SetUserNoteRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\PersonResourceIdentity;
use App\Http\Resources\SongResourceIdentity;
use App\Http\Resources\StudioResourceIdentity;
use App\Models\Episode;
use App\Models\User;
use App\Models\UserNote;
use App\Traits\Controller\WithStateVersionETag;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class UserNoteController extends Controller
{
    use WithStateVersionETag;

    /**
     * Returns the user's note-state Resources for the requested model IDs.
     *
     * @param GetUserNotesRequest $request
     * @param User                $user
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws InvalidEnumMemberException
     */
    public function overlay(GetUserNotesRequest $request, User $user): JsonResponse
    {
        if (auth()->id() !== $user->id) {
            throw new AuthorizationException(__('Notes are visible only to their owner.'));
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
        $relationshipKey = $this->relationshipKeyForReviewKind($reviewKind);
        $identityClass = $this->identityClassForReviewKind($reviewKind);

        // Episodes are addressed by public ID everywhere they are rendered.
        $publicIDsByKey = $morphClass === Episode::class
            ? Episode::withoutGlobalScopes()
                ->whereIn('public_id', $ids)
                ->pluck('public_id', 'id')
                ->all()
            : [];
        $modelKeys = $morphClass === Episode::class
            ? array_keys($publicIDsByKey)
            : $ids;

        $entries = [];

        if (!empty($modelKeys)) {
            UserNote::where('user_id', '=', $user->id)
                ->where('noteable_type', '=', $morphClass)
                ->whereIn('noteable_id', $modelKeys)
                ->select(['noteable_id', 'body', 'created_at', 'updated_at'])
                ->cursor()
                ->each(function ($row) use (&$entries, $relationshipKey, $identityClass, $publicIDsByKey, $morphClass) {
                    $identityValue = $morphClass === Episode::class
                        ? ($publicIDsByKey[$row->noteable_id] ?? null)
                        : $row->noteable_id;

                    if ($identityValue === null) {
                        return;
                    }

                    $entries[] = [
                        'attributes' => [
                            'body' => $row->body,
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
        }

        return JSONResult::success([
            'data' => $entries,
        ])->withHeaders($this->stateVersionHeaders($etag, $user));
    }

    /**
     * Writes the authenticated user's note on a model, clearing it when the body is empty.
     *
     * @param SetUserNoteRequest $request
     *
     * @return JsonResponse
     * @throws InvalidEnumMemberException
     */
    public function set(SetUserNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the model
        $reviewKind = ReviewKind::fromValue((int) $data['kind']);
        $morphClass = $reviewKind->getMorphClass();
        $model = $morphClass === Episode::class
            ? Episode::withoutGlobalScopes()
                ->where('public_id', '=', $data['model_id'])
                ->first()
            : $morphClass::withoutGlobalScopes()
                ->whereKey($data['model_id'])
                ->first();

        if ($model === null) {
            throw new AuthorizationException(__('The item you are trying to annotate no longer exists.'));
        }

        // A note needs no library entry and no rating, which is the whole point of it.
        $note = $user->setNote($model, $data['body'] ?? null);

        return JSONResult::success([
            'data' => [
                'attributes' => [
                    'body' => $note?->body,
                    'createdAt' => $note?->created_at?->timestamp,
                    'updatedAt' => $note?->updated_at?->timestamp,
                ],
            ],
        ]);
    }

    /**
     * Returns the per-type relationship key used in the overlay response for the given kind.
     *
     * @param ReviewKind $kind
     *
     * @return string
     */
    private function relationshipKeyForReviewKind(ReviewKind $kind): string
    {
        return match ($kind->value) {
            ReviewKind::Manga => 'literatures',
            ReviewKind::Game => 'games',
            ReviewKind::Character => 'characters',
            ReviewKind::Person => 'people',
            ReviewKind::Studio => 'studios',
            ReviewKind::Song => 'songs',
            ReviewKind::Episode => 'episodes',
            default => 'shows',
        };
    }

    /**
     * Returns the identity resource class used to render the noteable for the given kind.
     *
     * @param ReviewKind $kind
     *
     * @return string
     */
    private function identityClassForReviewKind(ReviewKind $kind): string
    {
        return match ($kind->value) {
            ReviewKind::Manga => LiteratureResourceIdentity::class,
            ReviewKind::Game => GameResourceIdentity::class,
            ReviewKind::Character => CharacterResourceIdentity::class,
            ReviewKind::Person => PersonResourceIdentity::class,
            ReviewKind::Studio => StudioResourceIdentity::class,
            ReviewKind::Song => SongResourceIdentity::class,
            ReviewKind::Episode => EpisodeResourceIdentity::class,
            default => AnimeResourceIdentity::class,
        };
    }
}
