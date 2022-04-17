<?php

namespace App\Console\Commands\Deleters;

use App\Models\Media;
use DirectoryIterator;
use Illuminate\Console\Command;
use SplFileInfo;

class DeleteStaleMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:stale_media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes media (images) on disk that aren’t linked in the database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $directoryPath = realpath('/Users/kirito/Docs/public');

        /**
         * @var int $key
         * @var SplFileInfo $directory
         */
        foreach (new DirectoryIterator($directoryPath) as $key => $directory) {
            $directoryName = $directory->getFilename();
            if (is_numeric($directoryName)) {
                $media = Media::firstWhere('id', '=', $directoryName);

                if (empty($media)) {
                    $trashPath = '/Users/kirito/.Trash/' . $directoryName;
                    $currentDirPath = $directory->getRealPath();

                    rename($currentDirPath, $trashPath);
                    echo 'moved ' . $directoryName . ' at ' . $currentDirPath . ' to ' . $trashPath . PHP_EOL;
                }
            }
        }

        return Command::SUCCESS;
    }
}
