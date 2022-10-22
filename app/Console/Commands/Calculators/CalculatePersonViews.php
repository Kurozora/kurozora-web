<?php

namespace App\Console\Commands\Calculators;

use App\Models\Person;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculatePersonViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:person_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an person.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Person::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Person::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Person::class)
            ->select(Person::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $persons) {
                /** @var Person $person */
                foreach ($persons as $person) {
                    $totalViewCount = $person->views_count + $person->views()->count();

                    $person->update([
                        'view_count' => $totalViewCount
                    ]);
                }
            });

        // Delete the calculated views
        View::where('viewable_type', '=', Person::class)
            ->forceDelete();

        return Command::SUCCESS;
    }
}
