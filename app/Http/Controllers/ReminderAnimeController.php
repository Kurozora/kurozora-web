<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\AddAnimeReminderRequest;
use App\Http\Requests\GetAnimeReminderRequest;
use App\Http\Resources\AnimeResource;
use App\Models\User;
use Auth;
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
        $user = Auth::user();

        // Paginate the reminder anime
        $reminderAnime = $user->reminderAnime()->paginate($data['limit'] ?? 25);

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
        $user = Auth::user();

        if (empty($user->receipt) || !$user->receipt->is_subscribed ?? true) {
            throw new AuthorizationException('Reminders are only available to pro users.');
        }

        $isAlreadyReminded = $user->userReminderAnime()->where('anime_id', $animeID)->exists();

        if ($isAlreadyReminded) { // Don't remind the user
            $user->reminderAnime()->detach($animeID);
        } else { // Remind the user
            $user->reminderAnime()->attach($animeID);
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
        /** @var User $user */
        $user = Auth::user();

        if (empty($user->receipt) || !$user->receipt->is_subscribed ?? true)
            throw new AuthorizationException('Reminders are only available to pro users.');

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
