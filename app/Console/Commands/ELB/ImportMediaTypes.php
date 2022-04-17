<?php

namespace App\Console\Commands\ELB;

use App\Models\MediaType;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_media_types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Media Types from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        MediaType::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $mediaTypes) {
                /** @var MediaType $mediaType */
                foreach ($mediaTypes as $mediaType) {
                    try {
                        MediaType::updateOrCreate([
                            'id' => $mediaType->id,
                            'type' => $mediaType->type,
                            'name' => $mediaType->name,
                        ], [
                            'id' => $mediaType->id,
                            'type' => $mediaType->type,
                            'name' => $mediaType->name,
                            'description' => $mediaType->description,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $mediaType->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $mediaType->id . PHP_EOL;
                }
            });

        return 0;
    }
}
