<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\AddAnimeReminderRequest;
use App\Http\Requests\GetAnimeReminderRequest;
use App\Http\Resources\AnimeResource;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Request;

class ReminderAnimeController extends Controller
{
    /**
     * Adds an anime to the user's reminders.
     *
     * @param AddAnimeReminderRequest $request
     * @param User $user
     * @return JsonResponse
     */
    function addReminder(AddAnimeReminderRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        $user->reminderAnime()->detach($data['anime_id']);

        if($data['is_reminded'])
            $user->reminderAnime()->attach($data['anime_id']);

        return JSONResult::success([
            'data' => [
                'is_reminded' => (bool) $data['is_reminded']
            ]
        ]);
    }

    /**
     * Returns a calendar file of the user's reminder anime.
     *
     * @param GetAnimeReminderRequest $request
     * @param User $user
     * @return JsonResponse
     */
    function getReminders(GetAnimeReminderRequest $request, User $user): JsonResponse
    {
        return JSONResult::success([
            'data' => AnimeResource::collection($user->reminderAnime()->get())
        ]);
    }

    /**
     * Serves the calendar file to be downloaded.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    function download(Request $request, User $user): \Illuminate\Http\Response
    {
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
