<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class GenerateSlug extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'generate:slug
            {model : Class name of model to bulk import}
            {--R|regenerate : Regenerate all slugs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for a model.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $chunkSize = 1000;
        $class = $this->argument('model');
        $regenerate = $this->option('regenerate');

        if ($regenerate) {
            $class::chunk($chunkSize, function (Collection $models) use ($class) {
                foreach ($models as $model) {
                    $model->generateSlug();
                    $model->save();
                }

                $this->line('<comment>Generated [' . $class . '] slugs up to ID:</comment> ' . $models->last()->id);
            });
        } else {
            $class::where('slug', '=', '')
                ->orWhere('slug', '=', null)
                ->chunk($chunkSize, function (Collection $models) use ($class) {
                    foreach ($models as $model) {
                        $model->generateSlug();
                        $model->save();
                    }

                    $this->line('<comment>Generated [' . $class . '] slugs up to ID:</comment> ' . $models->last()->id);
                });
        }

        $this->info('All [' . $class . '] slugs have been generated.');
        return Command::SUCCESS;
    }
}
