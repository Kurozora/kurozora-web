<?php

namespace App\Console\Commands;

use App\Session;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:delete_expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all expired sessions from the database';

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
     * @return mixed
     */
    public function handle()
    {
        Session::where('expiration_date', '<', Carbon::now())->delete();

        $this->info('Deleted all expired sessions.');
    }
}
