<?php

namespace App\Jobs;

use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBareBonesMangaAdded implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $malID;

    /**
     * Create a new job instance.
     *
     * @param string $malID
     */
    public function __construct(string $malID)
    {
        $this->malID = $malID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('scrape:mal_manga', ['malID' => $this->malID]);
    }
}
