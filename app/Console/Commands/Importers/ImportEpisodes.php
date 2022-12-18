<?php

namespace App\Console\Commands\Importers;

use App\Models\Episode;
use Illuminate\Console\Command;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

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
     * @throws InvalidArgumentException
     */
    public function handle(): int
    {
        $file = storage_path('app/episodes.json');
        $fileSize = filesize($file);
        $episodes = Items::fromFile($file, [
            'pointer' => '/data',
            'decoder' => new ExtJsonDecoder,
            'debug' => true
        ]);

        $progressBar = $this->output->createProgressBar($fileSize);
        $progressBar->start();

        foreach ($episodes as $episode) {
            Episode::firstOrCreate([
                'season_id' => 0,
                'number' => $episode->number,
                'number_total' => $episode->number + 0
            ],
            [
                'number' => $episode->number,
                'number_total' => $episode->number + 0,
                'title' => $episode->title,
                'synopsis' => $episode->synopsis ?? null,
                'ja' => [
                   'title' => $episode->japaneseTitle,
                   'synopsis' => null
                ],
                'duration' => 1440,
                'started_at' => $episode->firstAired,
                'is_verified' => true,
            ]);

            $progress = $episodes->getPosition();
            $progressBar->setProgress($progress);
        }

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
