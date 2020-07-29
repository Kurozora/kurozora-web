<?php

namespace App\Console\Commands;

use App\PasswordReset;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldPasswordResets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password_resets:delete_old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all old/invalid password resets';

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
    public function handle()
    {
        $compareTime = Carbon::now()->subHours(PasswordReset::VALID_HOURS);

        // Finds all old password resets and deletes them
        PasswordReset::where('created_at', '<', $compareTime)->delete();

        // Show info message
        $this->info('Deleted all old password resets.');

        return 1;
    }
}
