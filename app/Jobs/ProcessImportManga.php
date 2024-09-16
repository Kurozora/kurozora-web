<?php

namespace App\Jobs;

use App\Models\KDashboard\Manga as KManga;
use App\Services\ImportMangaProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportManga implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of manga to process.
     *
     * @var Collection|KManga[] $kMangas
     */
    protected Collection|array $kMangas;

    /**
     * Create a new job instance.
     *
     * @param Collection|KManga[] $kMangas
     */
    public function __construct(Collection|array $kMangas)
    {
        $this->kMangas = $kMangas;
    }

    /**
     * Execute the job.
     *
     * @param ImportMangaProcessor $importMangaProcessor
     * @return void
     */
    public function handle(ImportMangaProcessor $importMangaProcessor): void
    {
        $importMangaProcessor->process($this->kMangas);
    }
}
