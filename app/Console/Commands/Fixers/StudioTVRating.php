<?php

namespace App\Console\Commands\Fixers;

use App\Models\Studio;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

class StudioTVRating extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:studio_tv_rating';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix studios TV rating and NSFW status based on associated media.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 2000;

        Studio::withoutGlobalScopes()
            ->select(['id'])
            ->chunkById($chunkSize, function (Collection $studios) {
                $changedCount = Studio::refreshTVRatings($studios->modelKeys());

                $this->info('Updated ' . $changedCount . ' of ' . $studios->count() . ' studios up to ID ' . $studios->last()->id);
            });

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
