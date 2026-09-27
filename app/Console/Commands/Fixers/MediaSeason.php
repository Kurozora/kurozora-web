<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Telescope\Telescope;
use Pulse;

class MediaSeason extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:media_season {year? : The year whose anime, manga, and games should have their season and day fixed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix anime, manga, and games season and day based on their start date.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $year = $this->argument('year');

        if (!is_null($year) && !is_numeric($year)) {
            $this->error('Year must be a numeric value.');
            return Command::INVALID;
        }

        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 2000;
        $scheduleColumns = [
            Anime::class => ['air_season', 'air_day', 'started_at'],
            Manga::class => ['publication_season', 'publication_day', 'started_at'],
            Game::class => ['publication_season', 'publication_day', 'published_at'],
        ];

        foreach ($scheduleColumns as $modelClass => [$seasonColumn, $dayColumn, $dateColumn]) {
            $checkedCount = 0;
            $fixedCount = 0;

            $modelClass::withoutGlobalScopes()
                ->select(['id', $seasonColumn, $dayColumn, $dateColumn])
                ->when($year, function ($query) use ($dateColumn, $year) {
                    $query->whereYear($dateColumn, '=', $year);
                })
                ->chunkById($chunkSize, function (Collection $models) use ($modelClass, $seasonColumn, $dayColumn, $dateColumn, &$checkedCount, &$fixedCount) {
                    $checkedCount += $models->count();

                    DB::transaction(function () use ($models, $modelClass, $seasonColumn, $dayColumn, $dateColumn, &$fixedCount) {
                        $ids = [];
                        $seasonCases = '';
                        $dayCases = '';

                        /** @var Model|Anime|Manga|Game $model */
                        foreach ($models as $model) {
                            $season = $model instanceof Anime
                                ? $model->generateAiringSeason()
                                : $model->generatePublishingSeason();
                            $day = $model->{$dateColumn}?->dayOfWeek;
                            $currentSeason = $model->getRawOriginal($seasonColumn);
                            $currentSeason = is_null($currentSeason) ? null : (int) $currentSeason;
                            $currentDay = $model->getRawOriginal($dayColumn);
                            $currentDay = is_null($currentDay) ? null : (int) $currentDay;

                            if ($model instanceof Anime && !is_null($currentDay)) {
                                $day = $currentDay;
                            }

                            if ($currentSeason === $season && $currentDay === $day) {
                                continue;
                            }

                            $ids[] = (int) $model->id;
                            $seasonCases .= ' WHEN ' . (int) $model->id . ' THEN ' . ($season ?? 'NULL');
                            $dayCases .= ' WHEN ' . (int) $model->id . ' THEN ' . ($day ?? 'NULL');
                        }

                        if (empty($ids)) {
                            return;
                        }

                        $modelClass::withoutGlobalScopes()
                            ->whereIn('id', $ids)
                            ->update([
                                $seasonColumn => DB::raw('CASE id' . $seasonCases . ' END'),
                                $dayColumn => DB::raw('CASE id' . $dayCases . ' END'),
                            ]);

                        $fixedCount += count($ids);

                        $this->info('Fixed ' . count($ids) . ' ' . class_basename($modelClass) . ' schedules up to ID ' . $models->last()->id);
                    });
                });

            $this->info(class_basename($modelClass) . ($year ? ' in ' . $year : '') . ': checked ' . $checkedCount . ', fixed ' . $fixedCount);
        }

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
