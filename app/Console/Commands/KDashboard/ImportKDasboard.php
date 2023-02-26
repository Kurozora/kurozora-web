<?php

namespace App\Console\Commands\KDashboard;

use Artisan;
use Illuminate\Console\Command;

class ImportKDasboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:kdashbaord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports all data from KDashboard database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if ($this->confirm('Are you sure you want to import all data form ELB?')) {
            // Import main entities
            Artisan::call('import:kdashboard_anime');
//            Artisan::call('import:kdashboard_manga');
            Artisan::call('import:kdashboard_characters');
            Artisan::call('import:kdashboard_people');
            Artisan::call('import:kdashboard_songs');
            Artisan::call('import:kdashboard_studios');

            // Import anime entities
            Artisan::call('import:kdashboard_anime_cast');
            Artisan::call('import:kdashboard_media_songs');
            Artisan::call('import:kdashboard_anime_staff');
            Artisan::call('import:kdashboard_anime_studios');

            // Import misc entities
            Artisan::call('import:elb_media_relations');
            Artisan::call('import:elb_media_genres');
            Artisan::call('import:elb_media_themes');
            Artisan::call('import:elb_media_stats');
            Artisan::call('import:elb_media_ratings');
        }

        return Command::SUCCESS;
    }
}
