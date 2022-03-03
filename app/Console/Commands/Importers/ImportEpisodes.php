<?php

namespace App\Console\Commands\Importers;

use App\Models\Episode;
use Illuminate\Console\Command;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\JsonMachine;
use function storage_path;

class ImportEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime_episodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime episodes for files.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $file = storage_path('app/episodes.json');
        $fileSize = filesize($file);
        $episodes = JsonMachine::fromFile($file, '/data', new ExtJsonDecoder);

        $progressBar = $this->output->createProgressBar($fileSize);
        $progressBar->start();

        foreach ($episodes as $data) {
            Episode::firstOrCreate([
                'season_id' => 0,
                'number' => $data->number,
                'number_total' => $data->number + 0
            ],
            [
                'number' => $data->number,
                'number_total' => $data->number + 0,
                'title' => $data->title,
                'synopsis' => $data->synopsis ?? null,
                'ja' => [
                   'title' => $data->japaneseTitle,
                   'synopsis' => null
                ],
                'duration' => 1440,
                'first_aired' => $data->firstAired,
                'verified' => true,
            ]);

            $progress = $episodes->getPosition();
            $progressBar->setProgress($progress);
        }

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
