<?php

namespace App\Console\Commands\ELB;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports users from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        User::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $users) {
                /** @var User $user */
                foreach ($users as $user) {
                    try {
                        User::updateOrCreate([
                            'id' => $user->id,
                        ], [
                            'id' => $user->id,
                            'tv_rating' => $user->tv_rating,
                            'love_reacter_id' => $user->love_reacter_id,
                            'siwa_id' => $user->siwa_id,
                            'slug' => $user->slug,
                            'username' => $user->username,
                            'email' => $user->email,
                            'email_verified_at' => $user->email_verified_at,
                            'email_suspended' => $user->email_suspended,
                            'password' => $user->password,
                            'two_factor_secret' => $user->two_factor_secret,
                            'two_factor_recovery_codes' => $user->two_factor_recovery_codes,
                            'remember_token' => $user->remember_token,
                            'biography' => $user->biography,
                            'can_change_username' => $user->can_change_username,
                            'anime_imported_at' => $user->anime_imported_at,
                            'manga_imported_at' => $user->manga_imported_at,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $user->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $user->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
