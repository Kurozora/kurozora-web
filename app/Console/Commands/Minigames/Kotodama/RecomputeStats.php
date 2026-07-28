<?php

namespace App\Console\Commands\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Models\Minigames\Kotodama\Game;
use App\Models\User;
use App\Services\Minigames\Kotodama\StatsService;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;

class RecomputeStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kotodama:recompute-stats
                            {--user= : Only this user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild Kotodama stats from finished games.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $userIDs = Game::whereNotNull('user_id')
            ->whereIn('mode', [GameMode::Daily, GameMode::Archive])
            ->whereIn('status', [GameStatus::Won, GameStatus::Lost])
            ->when($this->option('user'), fn ($query) => $query->where('user_id', $this->option('user')))
            ->distinct()
            ->pluck('user_id');

        $recomputed = 0;

        foreach ($userIDs as $userID) {
            $user = User::find($userID);

            if (!$user) {
                continue;
            }

            $stats = StatsService::recompute($user);
            $recomputed++;

            $this->line($user->id . ': ' . $stats->games_played . ' played, ' . $stats->games_won . ' won, streak ' . $stats->current_streak . '.');
        }

        $this->info('Recomputed stats for ' . $recomputed . ' player(s).');

        return Command::SUCCESS;
    }
}
