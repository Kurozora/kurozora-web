<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddAnimeReminderRequest;
use App\Http\Requests\GetAnimeReminderRequest;
use App\Http\Resources\AnimeResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReminderAnimeController extends Controller
{
    /**
     * Returns a calendar file of the user's reminder anime.
     *
     * @param GetAnimeReminderRequest $request
     * @return JsonResponse
     */
    function getReminders(GetAnimeReminderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Paginate the reminder anime
        $reminderAnime = $user->reminder_anime()->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reminderAnime->nextPageUrl());

        // Show successful response
        return JSONResult::success([
            'data' => AnimeResource::collection($reminderAnime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds an anime to the user's reminders.
     *
     * @param AddAnimeReminderRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    function addReminder(AddAnimeReminderRequest $request): JsonResponse
    {
        $animeID = $request->input('anime_id');
        $user = auth()->user();

        if (!$user->is_subscribed) {
            throw new AuthorizationException('Reminders are only available to subscribed users.');
        }

        $isAlreadyReminded = $user->user_reminder_anime()->where('anime_id', $animeID)->exists();

        if ($isAlreadyReminded) { // Don't remind the user
            $user->reminder_anime()->detach($animeID);
        } else { // Remind the user
            $user->reminder_anime()->attach($animeID);
        }

        return JSONResult::success([
            'data' => [
                'isReminded' => !$isAlreadyReminded
            ]
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
            throw new AuthorizationException('Reminders are only available to subscribed users.');
        }

        $calendarExportStream = $user->getCalendar();

        // Headers to return for the download
        $headers = [
            'Content-type'          => 'text/calendar',
            'Content-Disposition'   => sprintf('attachment; filename=%s', $user->username. '-reminder.ics'),
            'Content-Length'        => strlen($calendarExportStream)
        ];

        // Return the file
        return Response::make($calendarExportStream, 200, $headers);
    }
}
