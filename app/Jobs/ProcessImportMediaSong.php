<?php

namespace App\Jobs;

use App\Models\KDashboard\Song as KSong;
use App\Services\ImportMediaSongProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportMediaSong implements ShouldQueue
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
     * @param ImportMediaSongProcessor $importMediaSongProcessor
     * @return void
     */
    public function handle(ImportMediaSongProcessor $importMediaSongProcessor): void
    {
        $importMediaSongProcessor->process($this->kSongs);
    }
}
