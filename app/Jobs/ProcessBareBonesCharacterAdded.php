<?php

namespace App\Jobs;

use Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBareBonesCharacterAdded implements ShouldQueue
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
        Artisan::call('scrape:mal_character', ['malID' => $this->malID]);
    }
}
