<?php

namespace App\Console\Commands;

use Database\Seeders\AnimeDummySeeder;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Console\Migrations\FreshCommand as BaseFreshCommand;
use Symfony\Component\Console\Input\InputOption;

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
        if ($this->needsRedownloading())
            $this->runRedownload();

        parent::handle();
    }

    /**
     * Determine if the developer has requested re-downloading anime.json file.
     *
     * @return bool
     */
    protected function needsRedownloading()
    {
        return $this->option('redownload');
    }

    /**
     * Run the re-download anime json command.
     *
     * @return void
     */
    protected function runRedownload() {
        $this->info('Downloading anime JSON...');
        AnimeDummySeeder::downloadJSON();
        $this->info('Anime JSON downloaded.');
    }

    protected function getOptions()
    {
        $options = parent::getOptions();
        array_push($options, ['redownload', null, InputOption::VALUE_NONE, 'Indicates if the anime.json should be re-downloaded.']);
        return $options;
    }
}
