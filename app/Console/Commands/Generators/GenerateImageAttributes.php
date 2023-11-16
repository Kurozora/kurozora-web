<?php

namespace App\Console\Commands\Generators;

use App\Jobs\ConvertImageToWebPJob;
use App\Jobs\GenerateImageAttributesJob;
use App\Models\Media;
use DB;
use Illuminate\Console\Command;

class GenerateImageAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:image-attr
                            {id : the id of the image}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a given image’s attributes.';

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
        DB::disableQueryLog();

        $id = $this->argument('id');

        if ($media = Media::firstWhere('id', '=', $id)) {
            (new GenerateImageAttributesJob($media))
                ->handle();

            if ($media->mime_type != 'image/webp') {
                (new ConvertImageToWebPJob($media))
                    ->handle();
            }
        }

        DB::enableQueryLog();
        return Command::SUCCESS;
    }
}
