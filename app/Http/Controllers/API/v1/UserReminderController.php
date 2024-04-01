<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\UserLibraryKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddAnimeReminderRequest;
use App\Http\Requests\GetAnimeReminderRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\GameResourceBasic;
use App\Http\Resources\LiteratureResourceBasic;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Traits\Model\Remindable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserReminderController extends Controller
{
    /**
     * Returns the lit of user's reminders.
     *
     * @param GetAnimeReminderRequest $request
     * @return JsonResponse
     */
    function index(GetAnimeReminderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get morph class
        $morphClass = match ((int) ($data['library'] ?? UserLibraryKind::Anime)) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        // Paginate the reminders
        $userReminders = $user->reminderAnime()
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating', 'mediaRatings' => function ($query) use ($user) {
                $query->where([
                    ['user_id', '=', $user->id],
                ]);
            }, 'library' => function ($query) use ($user) {
                $query->where('user_id', '=', $user->id);
            }])
            ->withExists([
                'favoriters as isFavorited' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                },
            ])
            ->when(in_array(Remindable::class, class_uses_recursive($morphClass)), function ($query) use ($user) {
                // Add your logic here if the trait is used
                $query->withExists([
                    'reminderers as isReminded' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    },
                ]);
            })
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $userReminders->nextPageUrl());

        // Get data collection
        $data = match ((int) ($data['library'] ?? UserLibraryKind::Anime)) {
            UserLibraryKind::Manga => LiteratureResourceBasic::collection($userReminders),
            UserLibraryKind::Game => GameResourceBasic::collection($userReminders),
            default => AnimeResourceBasic::collection($userReminders),
        };

        // Show successful response
        return JSONResult::success([
            'data' => $data,
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Adds an anime to the user's reminders.
     *
     * @param AddAnimeReminderRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    function create(AddAnimeReminderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        if (!$user->is_subscribed) {
            throw new AuthorizationException(__('Reminders are only available to subscribed users.'));
        }

        // Get the model
        if (!empty($data['anime_id'])) {
            $modelID = $data['anime_id'];
            $model = Anime::findOrFail($modelID);
        } else {
            $modelID = $data['model_id'];
            $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
            $model = match ($libraryKind->value) {
                UserLibraryKind::Manga  => Manga::findOrFail($modelID),
                UserLibraryKind::Game   => Game::findOrFail($modelID),
                default                 => Anime::findOrFail($modelID),
            };
        }

        $isAlreadyReminded = $user->user_reminder_anime()
            ->where('anime_id', $model->id)
            ->exists();

        if ($isAlreadyReminded) { // Don't remind the user
            $user->reminderAnime()->detach($model->id);
        } else { // Remind the user
            $user->reminderAnime()->attach($model->id);
        }

        return JSONResult::success([
            'data' => [
                'isReminded' => !$isAlreadyReminded,
            ],
        ]);
    }

    /**
     * Serves the calendar file to be downloaded.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    function download(Request $request): \Illuminate\Http\Response
    {
        $user = auth()->user();

        if (!$user->is_subscribed) {
            throw new AuthorizationException(__('Reminders are only available to subscribed users.'));
        }

        $startDate = now()->startOfWeek()->subWeeks(1);
        $endDate = now()->endOfWeek()->addWeeks(2);
        $whereBetween = [$startDate, $endDate];

        $user = $user->load([
            'reminderAnime' => function ($query) use ($whereBetween) {
                $query->with([
                    'translations',
                    'episodes' => function ($query) use ($whereBetween) {
                        $query->with(['translations'])
                            ->whereBetween(Episode::TABLE_NAME . '.started_at', $whereBetween);
                    },
                ]);
            },
        ]);
        $calendarExportStream = $user->getCalendar();

        // Headers to return for the download
        $headers = [
            'Content-type'          => 'text/calendar',
            'Content-Disposition'   => sprintf('attachment; filename=%s', $user->username. '-reminder.ics'),
            'Content-Length'        => strlen($calendarExportStream),
        ];

        // Return the file
        return Response::make($calendarExportStream, 200, $headers);
    }
}
