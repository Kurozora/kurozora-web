<?php

namespace App\Console\Commands\Calculators;

use App\Models\User;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateUserViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:user_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an user.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        User::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', User::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', User::class)
            ->select(User::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $users) {
                /** @var User $user */
                foreach ($users as $user) {
                    $totalViewCount = $user->views_count + $user->views()->count();

                    $user->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', User::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
