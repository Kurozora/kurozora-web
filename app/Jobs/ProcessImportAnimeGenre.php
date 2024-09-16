<?php

namespace App\Jobs;

use App\Models\KDashboard\MediaGenre as KMediaGenre;
use App\Services\ImportAnimeGenreProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportAnimeGenre implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of anime genres to process.
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
     * @param ImportAnimeGenreProcessor $importAnimeGenreProcessor
     * @return void
     */
    public function handle(ImportAnimeGenreProcessor $importAnimeGenreProcessor): void
    {
        $importAnimeGenreProcessor->process($this->kMediaGenres);
    }
}
