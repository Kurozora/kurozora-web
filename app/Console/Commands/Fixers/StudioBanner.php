<?php

namespace App\Console\Commands\Fixers;

use App\Models\Studio;
use Illuminate\Console\Command;

class StudioBanner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:studio_banner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes studio banners';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $studios = Studio::withoutGlobalScopes()
            ->whereHas('media', function ($query) {
                return $query->where('collection_name', '=', 'banner');
            }, '==', '0')
            ->pluck('id')
            ->implode(',');

        $this->call('generate:studio_banner', ['studioID' => $studios]);

        return Command::SUCCESS;
    }
}
