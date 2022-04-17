<?php

namespace App\Console\Commands\ELB;

use App\Models\Theme;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportThemes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_themes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports themes from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Theme::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $themes) {
                /** @var Theme $theme */
                foreach ($themes as $theme) {
                    try {
                        Theme::updateOrCreate([
                            'id' => $theme->id,
                        ], [
                            'id' => $theme->id,
                            'mal_id' => $theme->mal_id,
                            'tv_rating_id' => $theme->tv_rating_id,
                            'slug' => $theme->slug,
                            'name' => $theme->name,
                            'color' => $theme->color,
                            'description' => $theme->description,
                            'is_nsfw' => $theme->is_nsfw,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $theme->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $theme->id . PHP_EOL;
                }
            });

        return 0;
    }
}
