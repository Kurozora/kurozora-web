<?php

namespace App\Console\Commands\ELB;

use Artisan;
use Illuminate\Console\Command;

class ImportELB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports all data from ELB database.';

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
        if ($this->confirm('Are you sure you want to import all data form ELB?')) {
            // Import basic entities
            Artisan::call('import:elb_media_types');
            Artisan::call('import:elb_relations');
            Artisan::call('import:elb_cast_roles');
            Artisan::call('import:elb_sources');
            Artisan::call('import:elb_staff_roles');
            Artisan::call('import:elb_status');
            Artisan::call('import:elb_tv_ratings');

            // Import this shit
            Artisan::call('import:elb_love_reacters');

            // Import main entities
            Artisan::call('import:elb_users');
            Artisan::call('import:elb_anime');
            Artisan::call('import:elb_genres');
            Artisan::call('import:elb_themes');
//            Artisan::call('import:elb_manga');
            Artisan::call('import:elb_characters');
            Artisan::call('import:elb_people');
            Artisan::call('import:elb_songs');
            Artisan::call('import:elb_studios');

            // Import relate-able main entities
            Artisan::call('import:elb_seasons');
            Artisan::call('import:elb_episodes');

            // Import anime entities
            Artisan::call('import:elb_anime_cast');
            Artisan::call('import:elb_anime_songs');
            Artisan::call('import:elb_anime_staff');
            Artisan::call('import:elb_anime_studios');

            // Import misc entities
            Artisan::call('import:elb_media');
            Artisan::call('import:elb_media_relations');
            Artisan::call('import:elb_media_genres');
            Artisan::call('import:elb_media_themes');
            Artisan::call('import:elb_media_stats');
            Artisan::call('import:elb_media_ratings');
        }

        return Command::SUCCESS;
    }
}
