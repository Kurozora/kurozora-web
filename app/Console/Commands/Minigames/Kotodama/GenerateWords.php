<?php

namespace App\Console\Commands\Minigames\Kotodama;

use App\Services\Minigames\Kotodama\WordGenerator;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;

class GenerateWords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kotodama:generate-words
                            {--rank-limit=3000 : Rank ceiling for gated sources}
                            {--limit= : Cap on answers generated}
                            {--dry-run : Report without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mine the catalog for new Kotodama answers.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $rankLimit = max(1, (int) $this->option('rank-limit'));
        $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;
        $dryRun = (bool) $this->option('dry-run');

        $samples = [];
        $generated = WordGenerator::generate($rankLimit, $limit, $dryRun, $samples);

        if ($dryRun && $samples !== []) {
            $this->table(
                ['Answer', 'Subject'],
                collect($samples)
                    ->map(fn (string $subject, string $answer) => [$answer, $subject])
                    ->values()
                    ->all()
            );
        }

        $this->info($dryRun
            ? 'Would generate ' . $generated . ' answers.'
            : 'Generated ' . $generated . ' answers.');

        return Command::SUCCESS;
    }
}
