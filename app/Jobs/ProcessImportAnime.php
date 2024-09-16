<?php

namespace App\Jobs;

use App\Models\KDashboard\Anime as KAnime;
use App\Services\ImportAnimeProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportAnime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of anime to process.
     *
     * @var Collection|KAnime[] $kAnimes
     */
    protected Collection|array $kAnimes;

    /**
     * Create a new job instance.
     *
     * @param Collection|KAnime[] $kAnimes
     */
    public function __construct(Collection|array $kAnimes)
    {
        $this->kAnimes = $kAnimes;
    }

    /**
     * Execute the job.
     *
     * @param ImportAnimeProcessor $importAnimeProcessor
     * @return void
     */
    public function handle(ImportAnimeProcessor $importAnimeProcessor): void
    {
        $importAnimeProcessor->process($this->kAnimes);
    }
}
