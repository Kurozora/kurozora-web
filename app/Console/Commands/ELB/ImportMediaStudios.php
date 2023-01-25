<?php

namespace App\Console\Commands\ELB;

use App\Models\MediaStudio;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaStudios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_media_studios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports media studios from the ELB database.';

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
        MediaStudio::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $mediaStudios) {
                /** @var MediaStudio $mediaStudio */
                foreach ($mediaStudios as $mediaStudio) {
                    try {
                        MediaStudio::updateOrCreate([
                            'model_type' => $mediaStudio->model_type,
                            'model_id' => $mediaStudio->model_id,
                            'studio_id' => $mediaStudio->studio_id,
                        ], [
                            'id' => $mediaStudio->id,
                            'model_type' => $mediaStudio->model_type,
                            'model_id' => $mediaStudio->model_id,
                            'studio_id' => $mediaStudio->studio_id,
                            'is_licensor' => $mediaStudio->is_licensor,
                            'is_producer' => $mediaStudio->is_producer,
                            'is_studio' => $mediaStudio->is_studio,
                            'is_publisher' => $mediaStudio->is_publisher,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $mediaStudio->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $mediaStudio->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
