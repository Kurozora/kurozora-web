<?php

namespace App\Console\Commands\Scout;

use App\Models\Anime;
use App\Models\AppTheme;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use App\Models\UserLibrary;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Scout\Console\ImportCommand;

class Import extends ImportCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:import
                            {model : Class name of model to bulk import}
                            {--c|chunk= : The number of records to import at a time (Defaults to configuration value: `scout.chunk.searchable`)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the given model into the search index';

    /**
     * Execute the console command.
     *
     * @param Dispatcher $events
     * @return int
     */
    public function handle(Dispatcher $events): int
    {
        $class = $this->argument('model');

        if ($class !== 'all') {
            parent::handle($events);
            return Command::SUCCESS;
        }

        $this->call('scout:import', ['model' => User::class]);
        $this->call('scout:import', ['model' => UserLibrary::class]);
        $this->call('scout:import', ['model' => Anime::class]);
        $this->call('scout:import', ['model' => AppTheme::class]);
        $this->call('scout:import', ['model' => Manga::class]);
        $this->call('scout:import', ['model' => Game::class]);
        $this->call('scout:import', ['model' => Song::class]);
        $this->call('scout:import', ['model' => Studio::class]);
        $this->call('scout:import', ['model' => Character::class]);
        $this->call('scout:import', ['model' => Person::class]);
        $this->call('scout:import', ['model' => Episode::class]);

        return Command::SUCCESS;
    }
}
