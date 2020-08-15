<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\AddAnimeReminderRequest;
use App\Http\Requests\DownloadAnimeReminderRequest;
use App\Http\Requests\GetAnimeReminderRequest;
use App\Http\Resources\AnimeResource;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

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
        $animeID = $request->input('anime_id');

        $isAlreadyReminded = $user->userReminderAnime()->where('anime_id', $animeID)->exists();

        if($isAlreadyReminded) // Don't remind the user
            $user->reminderAnime()->detach($animeID);
        else // Remind the user
            $user->reminderAnime()->attach($animeID);

        return JSONResult::success([
            'data' => [
                'isReminded' => !$isAlreadyReminded
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
     * @param DownloadAnimeReminderRequest $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    function download(DownloadAnimeReminderRequest $request, User $user): \Illuminate\Http\Response
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
