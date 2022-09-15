<?php

namespace App\Console\Commands\Calculators;

use App\Models\Character;
use App\Models\View;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CalculateCharacterViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:character_views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the total views of an character.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Character::with('views')
            ->join(View::TABLE_NAME, View::TABLE_NAME . '.viewable_id', '=', Character::TABLE_NAME . '.id')
            ->where(View::TABLE_NAME . '.viewable_type', Character::class)
            ->select(Character::TABLE_NAME . '.*')
            ->groupBy('id')
            ->chunk(1000, function (Collection $characters) {
                /** @var Character $character */
                foreach ($characters as $character) {
                    $character->update([
                        'view_count' => $character->views()->count()
                    ]);
                }
            });

        return Command::SUCCESS;
    }
}
