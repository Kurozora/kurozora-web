<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteInactiveUnconfirmedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete_inactive_unconfirmed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes users that have not confirmed their email within 24 hours';

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
        User::where('email_confirmation_id', '!=', null)
            ->whereDate('created_at', '<', Carbon::now()->subHours(24))
            ->delete();

        $this->info('Deleted all inactive unconfirmed users.');
    }
}
