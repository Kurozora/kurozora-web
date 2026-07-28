<?php

namespace App\Console\Commands\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Services\Minigames\Kotodama\PuzzleResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class ScheduleDailyPuzzles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kotodama:schedule
                            {--date= : ISO start date to schedule (defaults to tomorrow)}
                            {--days=1 : Number of consecutive days to schedule forward}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule the next N daily Kotodama puzzles.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $cursor = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::tomorrow();

        $days = max(1, (int) $this->option('days'));

        for ($day = 0; $day < $days; $day++) {
            $date = $cursor->toDateString();

            if (DailyPuzzle::where('puzzle_date', $date)->exists()) {
                $cursor->addDay();
                continue;
            }

            try {
                $word = PuzzleResolver::pickNextWord();
            } catch (Throwable $exception) {
                $this->error($exception->getMessage());
                return Command::FAILURE;
            }

            $puzzleNumber = (int) DailyPuzzle::max('puzzle_number') + 1;

            DailyPuzzle::create([
                'word_id' => $word->id,
                'puzzle_date' => $date,
                'puzzle_number' => $puzzleNumber,
            ]);

            $this->info('Scheduled #' . $puzzleNumber . ' for ' . $date . '.');
            $cursor->addDay();
        }

        return Command::SUCCESS;
    }
}
