<?php

namespace App\Console\Commands;

use AnimesTableDummySeeder;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Console\Migrations\FreshCommand as BaseFreshCommand;
use Illuminate\Support\Facades\Storage;

class FreshCommand extends BaseFreshCommand
{
    use ConfirmableTrait;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('seed')) {
            $pathToAnimeJSON = AnimesTableDummySeeder::ANIME_JSON_PATH;
            if (Storage::exists($pathToAnimeJSON))
                Storage::delete($pathToAnimeJSON);
        }

        parent::handle();
    }
}
