<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\AddAnimeReminderRequest;
use App\Http\Resources\AnimeResource;
use App\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReminderAnimeController extends Controller
{
    /**
     * Adds an anime to the user's reminders.
     *
     * @param AddAnimeReminderRequest $request
     * @return JsonResponse
     */
    function addReminder(AddAnimeReminderRequest $request): JsonResponse
    {
        $animeID = $request->input('anime_id');

        /** @var User $user */
        $user = Auth::user();

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
     * @param Request $request
     * @return JsonResponse
     */
    function getReminders(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return JSONResult::success([
            'data' => AnimeResource::collection($user->reminderAnime()->get())
        ]);
    }

    /**
     * Serves the calendar file to be downloaded.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    function download(Request $request): \Illuminate\Http\Response
    {
        /** @var User $user */
        $user = Auth::user();

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
