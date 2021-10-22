<?php

namespace App\Console\Commands;

use App\Models\Session;
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
     * @return int
     */
    public function handle(): int
    {
        Session::where('expires_at', '<', Carbon::now())->delete();

        $this->info('Deleted all expired sessions.');

        return Command::SUCCESS;
    }
}
