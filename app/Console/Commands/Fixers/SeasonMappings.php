<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use App\Models\Season;
use DB;
use Illuminate\Console\Command;

class SeasonMappings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:season_mappings {--dry-run : Preview without writing} {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill tvdb_season on single-season anime whose tvdb_id is not shared with any sibling anime.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $uniqueTvdbIDs = Anime::withoutGlobalScopes()
            ->whereNotNull('tvdb_id')
            ->groupBy('tvdb_id')
            ->havingRaw('COUNT(*) = 1')
            ->select('tvdb_id');

        $query = Season::withoutGlobalScopes()
            ->whereNull('tvdb_season')
            ->whereHas('anime', function ($builder) use ($uniqueTvdbIDs) {
                $builder->withoutGlobalScopes()
                    ->where('season_count', '=', 1)
                    ->whereIn('tvdb_id', $uniqueTvdbIDs);
            });

        $count = $query->count();

        if ($count === 0) {
            $this->info('Nothing to backfill.');
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('🧪 DRY-RUN: ' . $count . ' season(s) would be mapped (tvdb_season=number, tvdb_episode_offset=0).');
            return Command::SUCCESS;
        }

        if (!$force && !$this->confirm('Map ' . $count . ' season(s) with tvdb_season=number, tvdb_episode_offset=0?', false)) {
            $this->info('Aborted.');
            return Command::SUCCESS;
        }

        $updated = $query->update([
            'tvdb_season' => DB::raw(Season::TABLE_NAME . '.number'),
            'tvdb_episode_offset' => 0,
        ]);

        $this->info('Mapped ' . $updated . ' season(s).');

        return Command::SUCCESS;
    }
}
