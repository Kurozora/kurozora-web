<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\UserLibraryKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserReminderRequest;
use App\Http\Requests\GetUserReminderRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\GameResourceBasic;
use App\Http\Resources\LiteratureResourceBasic;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\UserLibrary;
use App\Models\UserReminder;
use App\Traits\Model\Remindable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserReminderController extends Controller
{
    /**
     * Returns the list of user's reminders.
     *
     * @param GetUserReminderRequest $request
     *
     * @return JsonResponse
     */
    function index(GetUserReminderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $library = (int) ($data['library'] ?? UserLibraryKind::Anime);

        // Get the authenticated user
        $user = auth()->user();

        // Get morph class
        $morphClass = match ($library) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        // Paginate the reminded model
        $userReminders = $user->whereReminded($morphClass)
            ->when(auth()->user() !== $user, function (Builder $query) use ($user) {
                $query->join(UserLibrary::TABLE_NAME, UserReminder::TABLE_NAME . '.remindable_id', '=', UserLibrary::TABLE_NAME . '.trackable_id')
                    ->whereColumn(UserLibrary::TABLE_NAME . '.trackable_type', '=', UserReminder::TABLE_NAME . '.remindable_type')
                    ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $user->id)
                    ->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
            })
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin', 'mediaRatings' => function ($query) use ($user) {
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
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $userReminders->nextPageUrl() ?? '');

        // Get data collection
        $data = match ($library) {
            UserLibraryKind::Manga => ['literatures' => LiteratureResourceBasic::collection($userReminders)],
            UserLibraryKind::Game => ['games' => GameResourceBasic::collection($userReminders)],
            default => ['shows' => AnimeResourceBasic::collection($userReminders)],
        };

        // Show successful response
        return JSONResult::success([
            'data' => $data,
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Adds a model to the user's reminders.
     *
     * @param CreateUserReminderRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    function create(CreateUserReminderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        if (!$user->is_subscribed) {
            throw new AuthorizationException(__('Reminders are only available to subscribed users.'));
        }

        // Get the model
        $modelID = $data['model_id'];
        $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
        $model = match ($libraryKind->value) {
            UserLibraryKind::Manga => Manga::findOrFail($modelID),
            UserLibraryKind::Game => Game::findOrFail($modelID),
            default => Anime::findOrFail($modelID),
        };

        // Successful response
        return JSONResult::success([
            'data' => [
                'isReminded' => !is_bool($user->toggleReminder($model)),
            ],
        ]);
    }

    /**
     * Serves the calendar file to be downloaded.
     *
     * @param Request $request
     *
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
            'reminders' => function ($query) use ($whereBetween) {
                $query->where('remindable_type', '=', Anime::class)
                    ->with([
                        'remindable' => function ($query) use ($whereBetween) {
                            $query->with([
                                'translation',
                                'episodes' => function ($query) use ($whereBetween) {
                                    $query->with(['translation'])
                                        ->whereBetween(Episode::TABLE_NAME . '.started_at', $whereBetween);
                                }
                            ]);
                        }
                    ]);
            },
        ]);
        $calendarExportStream = $user->getCalendar();

        // Headers to return for the download
        $headers = [
            'Content-type' => 'text/calendar',
            'Content-Disposition' => sprintf('attachment; filename=%s', $user->username . '-reminders.ics'),
            'Content-Length' => strlen($calendarExportStream),
        ];

        // Return the file
        return Response::make($calendarExportStream, 200, $headers);
    }
}
