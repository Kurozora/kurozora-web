<?php

namespace App\Console\Commands\Scrapers\TVDB;

use App\Models\Anime;
use App\Models\Season;
use App\Processors\TVDB\EpisodeProcessor;
use App\Spiders\TVDB\EpisodeSpider;
use DB;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Episode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:tvdb_episode {tvdbID? : TVDB series ids} {--season= : TVDB season to limit to} {--kurozora-season= : Kurozora season ids to limit to} {--rebuild-chain : Overwrite existing episode chain links} {--dry-run : Preview without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape TVDB episodes into mapped Kurozora seasons.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $tvdbIDs = $this->argument('tvdbID');

        if (empty($tvdbIDs)) {
            $tvdbIDs = $this->ask('TVDB id');
        }

        $tvdbIDs = explode(',', $tvdbIDs);

        if (empty($tvdbIDs)) {
            $this->info('ID is empty. Exiting...');

            Pulse::startRecording();
            Telescope::startRecording();

            return Command::INVALID;
        }

        $singleSeason = $this->option('season') !== null ? (int) $this->option('season') : null;
        $dryRun = (bool) $this->option('dry-run');
        $rebuildChain = (bool) $this->option('rebuild-chain');

        if ($rebuildChain) {
            $this->warn('🔗 --rebuild-chain: existing next/previous_episode_id values will be overwritten.');
        }

        $kurozoraSeasonOption = $this->option('kurozora-season');
        $kurozoraSeasonIDs = $kurozoraSeasonOption !== null
            ? array_values(array_filter(array_map('intval', explode(',', $kurozoraSeasonOption))))
            : [];

        $allowedSeasonIDs = [];
        $derivedTvdbSeasons = [];
        if (!empty($kurozoraSeasonIDs)) {
            $mappedSeasons = Season::withoutGlobalScopes()
                ->whereIn('id', $kurozoraSeasonIDs)
                ->whereNotNull('tvdb_season')
                ->get(['id', 'tvdb_season']);

            $allowedSeasonIDs = $mappedSeasons->pluck('id')->map(fn ($id) => (int) $id)->all();
            $derivedTvdbSeasons = $mappedSeasons->pluck('tvdb_season')->map(fn ($number) => (int) $number)->unique()->values()->all();

            $unmapped = array_diff($kurozoraSeasonIDs, $allowedSeasonIDs);
            if (!empty($unmapped)) {
                $this->warn('Skipping unmapped Kurozora season IDs (tvdb_season is NULL or season does not exist): ' . implode(', ', $unmapped));
            }

            if (empty($allowedSeasonIDs)) {
                $this->error('None of the provided --kurozora-season IDs have a tvdb_season mapping. Set the mapping in Nova first. Exiting.');

                Pulse::startRecording();
                Telescope::startRecording();

                return Command::INVALID;
            }
        }

        if ($dryRun) {
            $this->warn('🧪 DRY-RUN: no database writes will be performed.');
        }

        $tvdbSeasonsToFetch = $derivedTvdbSeasons;
        if ($singleSeason !== null) {
            $tvdbSeasonsToFetch = empty($tvdbSeasonsToFetch)
                ? [$singleSeason]
                : array_values(array_intersect($tvdbSeasonsToFetch, [$singleSeason]));

            if (empty($tvdbSeasonsToFetch)) {
                $this->error('--season=' . $singleSeason . ' does not intersect with the TVDB seasons derived from --kurozora-season. Exiting.');

                Pulse::startRecording();
                Telescope::startRecording();

                return Command::INVALID;
            }
        }

        if (!empty($tvdbSeasonsToFetch)) {
            $this->line('Fetching TVDB seasons: ' . implode(', ', $tvdbSeasonsToFetch));
        }
        if (!empty($allowedSeasonIDs)) {
            $this->line('Writes gated to Kurozora season IDs: ' . implode(', ', $allowedSeasonIDs));
        }

        $urls = [];
        foreach ($tvdbIDs as $tvdbID) {
            $urls[] = config('scraper.domains.tvdb.dereferrer.series') . '/' . $tvdbID;
        }

        $context = [];
        if (!empty($tvdbSeasonsToFetch)) {
            $context['tvdbSeasons'] = $tvdbSeasonsToFetch;
        }
        if ($dryRun) {
            $context['dryRun'] = true;
        }

        $processorOptions = ['dryRun' => $dryRun];
        if (!empty($allowedSeasonIDs)) {
            $processorOptions['allowedSeasonIDs'] = $allowedSeasonIDs;
        }

        Roach::startSpider(
            EpisodeSpider::class,
            new Overrides(
                startUrls: $urls,
                itemProcessors: [EpisodeProcessor::withOptions($processorOptions)],
            ),
            $context
        );

        foreach ($tvdbIDs as $tvdbID) {
            $animeIDs = Anime::withoutGlobalScopes()
                ->where('tvdb_id', '=', $tvdbID)
                ->whereHas('seasons', function ($query) {
                    $query->whereNotNull('tvdb_season');
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($animeIDs)) {
                continue;
            }

            $franchiseEpisodes = \App\Models\Episode::withoutGlobalScopes()
                ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
                ->whereIn('seasons.anime_id', $animeIDs)
                ->whereNotNull('seasons.tvdb_season')
                ->whereNull('seasons.deleted_at')
                ->orderBy('episodes.number_total')
                ->orderBy('episodes.season_id')
                ->orderBy('episodes.number')
                ->get(['episodes.id', 'episodes.season_id', 'seasons.anime_id']);

            $episodeCountByAnime = $franchiseEpisodes->countBy(fn ($row) => (int) $row->anime_id);
            $seasonCountByAnime = $franchiseEpisodes
                ->groupBy(fn ($row) => (int) $row->anime_id)
                ->map(fn ($rows) => $rows->pluck('season_id')->unique()->count());

            if ($dryRun) {
                foreach ($animeIDs as $animeID) {
                    $this->line('🧪 [DRY-RUN] anime ' . $animeID
                        . ': would set season_count=' . ($seasonCountByAnime[$animeID] ?? 0)
                        . ', episode_count=' . ($episodeCountByAnime[$animeID] ?? 0));
                }
                $chainMode = $rebuildChain ? 'REBUILD (overwrite)' : 'NULL-fill (preserve existing)';
                $this->line('🧪 [DRY-RUN] tvdb ' . $tvdbID . ': would ' . $chainMode . ' ' . $franchiseEpisodes->count() . ' franchise episodes across ' . count($animeIDs) . ' anime (next/previous_episode_id)');
                continue;
            }

            $seasonCases = '';
            $episodeCases = '';
            foreach ($animeIDs as $animeID) {
                $seasonCases .= ' WHEN ' . $animeID . ' THEN ' . (int) ($seasonCountByAnime[$animeID] ?? 0);
                $episodeCases .= ' WHEN ' . $animeID . ' THEN ' . (int) ($episodeCountByAnime[$animeID] ?? 0);
            }
            Anime::withoutGlobalScopes()
                ->whereIn('id', $animeIDs)
                ->update([
                    'season_count' => DB::raw('CASE id' . $seasonCases . ' END'),
                    'episode_count' => DB::raw('CASE id' . $episodeCases . ' END'),
                ]);

            $episodeIDs = [];
            $nextCases = '';
            $previousCases = '';
            $totalEpisodes = $franchiseEpisodes->count();

            foreach ($franchiseEpisodes as $key => $episode) {
                $nextEpisode = $key !== $totalEpisodes - 1 ? (int) $franchiseEpisodes[$key + 1]->id : null;
                $previousEpisode = $key !== 0 ? (int) $franchiseEpisodes[$key - 1]->id : null;

                $episodeIDs[] = (int) $episode->id;
                $nextCases .= ' WHEN ' . (int) $episode->id . ' THEN ' . ($nextEpisode === null ? 'NULL' : $nextEpisode);
                $previousCases .= ' WHEN ' . (int) $episode->id . ' THEN ' . ($previousEpisode === null ? 'NULL' : $previousEpisode);
            }

            if (!empty($episodeIDs)) {
                $nextExpression = $rebuildChain
                    ? 'CASE id' . $nextCases . ' END'
                    : 'COALESCE(next_episode_id, CASE id' . $nextCases . ' END)';
                $previousExpression = $rebuildChain
                    ? 'CASE id' . $previousCases . ' END'
                    : 'COALESCE(previous_episode_id, CASE id' . $previousCases . ' END)';

                \App\Models\Episode::withoutGlobalScopes()
                    ->whereIn('id', $episodeIDs)
                    ->update([
                        'next_episode_id' => DB::raw($nextExpression),
                        'previous_episode_id' => DB::raw($previousExpression),
                    ]);
            }
        }

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
