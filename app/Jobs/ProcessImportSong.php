<?php

namespace App\Jobs;

use App\Models\KDashboard\Song as KSong;
use App\Services\ImportSongProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportSong implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of people to process.
     *
     * @var Collection|KSong[] $kSongs
     */
    protected Collection|array $kSongs;

    /**
     * Create a new job instance.
     *
     * @param Collection|array $kSongs
     */
    public function __construct(Collection|array $kSongs)
    {
        $this->kSongs = $kSongs;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ImportSongProcessor $importSongProcessor)
    {
        $importSongProcessor->process($this->kSongs);
    }
}
