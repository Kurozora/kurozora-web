<?php

namespace App\Console\Commands\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\User;
use App\Services\Minigames\Kotodama\StatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Laravel\Telescope\Telescope;
use Pulse;

class SweepAbandoned extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kotodama:sweep-abandoned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark any still-in-progress daily games from yesterday as lost.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $yesterday = Carbon::yesterday()->toDateString();

        $puzzleIDs = DailyPuzzle::where('puzzle_date', $yesterday)
            ->pluck('id');

        if ($puzzleIDs->isEmpty()) {
            $this->line("No puzzles for {$yesterday}, nothing to sweep.");

            Pulse::startRecording();
            Telescope::startRecording();

            return Command::SUCCESS;
        }

        $games = Game::whereIn('daily_puzzle_id', $puzzleIDs)
            ->where('mode', GameMode::Daily)
            ->where('status', GameStatus::InProgress)
            ->get(['id', 'user_id']);

        if ($games->isNotEmpty()) {
            Game::whereIn('id', $games->pluck('id'))
                ->update([
                    'status' => GameStatus::Lost(),
                    'finished_at' => Carbon::now(),
                ]);
        }

        $userIDs = $games->pluck('user_id')
            ->filter()
            ->unique()
            ->all();

        foreach ($userIDs as $userID) {
            $user = User::find($userID);

            if ($user) {
                StatsService::recompute($user);
            }
        }

        $this->info('Swept ' . $games->count() . ' abandoned daily games.');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
