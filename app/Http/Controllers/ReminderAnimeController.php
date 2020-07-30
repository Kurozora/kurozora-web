<?php

namespace App\Http\Controllers;

use App\Anime;
use App\Enums\DayOfWeek;
use App\Helpers\JSONResult;
use App\Http\Requests\AddAnimeReminderRequest;
use App\Http\Requests\GetAnimeReminderRequest;
use App\Http\Resources\AnimeResource;
use App\User;
use App\UserReminderAnime;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Response;
use Ramsey\Uuid\Uuid;
use Request;
use Spatie\IcalendarGenerator\Components\Alert;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\PropertyTypes\Parameter;
use Spatie\IcalendarGenerator\PropertyTypes\TextPropertyType;

class ReminderAnimeController extends Controller
{
    /**
     * Adds an anime to the user's reminders.
     *
     * @param AddAnimeReminderRequest $request
     * @param User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    function addReminder(AddAnimeReminderRequest $request, User $user) {
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    function getReminders(GetAnimeReminderRequest $request, User $user) {
        return JSONResult::success([
            'data' => [
                AnimeResource::collection($user->reminderAnime()->get())
            ]
        ]);
    }

    /**
     * Serves the calendar file to be downloaded.
     *
     * @param Request $request
     * @param User $user
     *
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    function download(Request $request, User $user) {
        /** @var Anime[] $animes */
        $animes = $user->reminderAnime()->get();
        $appName = env('APP_NAME');
        $productIdentifier = '-//Kurozora B.V.//' . $appName . '//'. strtoupper(config('app.locale'));

        $calendar = Calendar::create(UserReminderAnime::CAL_NAME);
        $calendar->description(UserReminderAnime::CAL_DESCRIPTION)
            ->productIdentifier($productIdentifier)
            ->refreshInterval(UserReminderAnime::CAL_REFRESH_INTERVAL)
            ->appendProperty(TextPropertyType::create('CALSCALE', 'GREGORIAN'))
            ->appendProperty(TextPropertyType::create('X-APPLE-CALENDAR-COLOR', '#FF9300'))
            ->appendProperty(TextPropertyType::create('COLOR', 'orange'))
            ->appendProperty(TextPropertyType::create('ORGANIZER', 'kurozoraapp@gmail.app')
                ->addParameter(Parameter::create('CN', 'Kurozora')));

        foreach ($animes as $anime) {
            $startDate = $anime->first_aired->setTimeFrom(Carbon::createFromFormat('H:i:s', $anime->air_time))->setTimezone('Asia/Tokyo');
            $episodeCount = $anime->episode_count;
            $episodeRange = range(1, $episodeCount);

            foreach (array_slice($episodeRange, -4, null, true) as $episodeNum) {
                $uniqueIdentifier = Uuid::uuid4() . '@kurozora.app';
                $endDate = clone $startDate;
                $endDate->addMinutes($anime->runtime);

                // Create event
                $calendarEvent = Event::create($anime->title . ' Episode ' . $episodeNum)
                    ->description($anime->synopsis)
                    ->organizer('kurozoraapp@gmail.com', 'Kurozora')
                    ->startsAt(clone $startDate)
                    ->endsAt($endDate)
                    ->uniqueIdentifier($uniqueIdentifier);

                // Add custom properties
                $calendarEvent->appendProperty(TextPropertyType::create('URL', route('anime', $anime)))
                    ->appendProperty(TextPropertyType::create('X-APPLE-TRAVEL-ADVISORY-BEHAVIOR', 'AUTOMATIC'));

                // Add alerts
                $firstReminderMessage = $anime->title . ' starts in ' . UserReminderAnime::CAL_FIRST_ALERT_MINUTES . ' minutes.';
                $secondReminderMessage = $anime->title . ' starts in ' . UserReminderAnime::CAL_SECOND_ALERT_MINUTES . ' minutes.';
                $thirdReminderMessage = $anime->title . ' starts in ' . UserReminderAnime::CAL_THIRD_ALERT_DAY . ' day.';

                $firstAlert = Alert::minutesBeforeStart(UserReminderAnime::CAL_FIRST_ALERT_MINUTES)
                    ->message($firstReminderMessage)
                    ->appendProperty(TextPropertyType::create('UID', Uuid::uuid4() . '@kurozora.app'));
                $secondAlert = Alert::minutesBeforeStart(UserReminderAnime::CAL_SECOND_ALERT_MINUTES)
                    ->message($secondReminderMessage)
                    ->appendProperty(TextPropertyType::create('UID', Uuid::uuid4() . '@kurozora.app'));
                $thirdAlert = Alert::minutesBeforeStart(UserReminderAnime::CAL_THIRD_ALERT_DAY)
                    ->message($thirdReminderMessage)
                    ->appendProperty(TextPropertyType::create('UID', Uuid::uuid4() . '@kurozora.app'));

                $calendarEvent->alert($firstAlert)
                    ->alert($secondAlert)
                    ->alert($thirdAlert);

                // Add event to calendar
                $calendar->event($calendarEvent);

                // Advance start date
                $startDate->addWeeks(1);
            }
        }

        // Prepare for exporting
        $calendarExportStream = $calendar->get();

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
