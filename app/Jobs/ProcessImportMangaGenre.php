<?php

namespace App\Jobs;

use App\Models\KDashboard\MediaGenre as KMediaGenre;
use App\Services\ImportMangaGenreProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportMangaGenre implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of manga genres to process.
     *
     * @var Collection|KMediaGenre[] $kMediaGenres
     */
    protected Collection|array $kMediaGenres;

    /**
     * Create a new job instance.
     *
     * @param Collection|KMediaGenre[] $kMediaGenres
     */
    public function __construct(Collection|array $kMediaGenres)
    {
        $this->kMediaGenres = $kMediaGenres;
    }

    /**
     * Execute the job.
     *
     * @param ImportMangaGenreProcessor $importMangaGenreProcessor
     * @return void
     */
    public function handle(ImportMangaGenreProcessor $importMangaGenreProcessor): void
    {
        $importMangaGenreProcessor->process($this->kMediaGenres);
    }
}
