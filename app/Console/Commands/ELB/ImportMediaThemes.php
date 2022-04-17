<?php

namespace App\Console\Commands\ELB;

use App\Models\MediaTheme;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaThemes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_media_themes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports media themes from the ELB database.';

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
        MediaTheme::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $mediaThemes) {
                /** @var MediaTheme $mediaTheme */
                foreach ($mediaThemes as $mediaTheme) {
                    try {
                        MediaTheme::updateOrCreate([
                            'model_id' => $mediaTheme->model_id,
                            'model_type' => $mediaTheme->model_type,
                            'theme_id' => $mediaTheme->theme_id,
                        ], [
                            'id' => $mediaTheme->id,
                            'model_id' => $mediaTheme->model_id,
                            'model_type' => $mediaTheme->model_type,
                            'theme_id' => $mediaTheme->theme_id,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $mediaTheme->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $mediaTheme->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
