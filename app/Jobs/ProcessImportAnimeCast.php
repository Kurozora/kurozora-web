<?php

namespace App\Jobs;

use App\Models\KDashboard\AnimeCharacter as KAnimeCast;
use App\Services\ImportAnimeCastProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportAnimeCast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of anime cast to process.
     *
     * @var Collection|KAnimeCast[] $kAnimeCasts
     */
    protected Collection|array $kAnimeCasts;

    /**
     * Create a new job instance.
     *
     * @param Collection|KAnimeCast[] $kAnimeCasts
     */
    public function __construct(Collection|array $kAnimeCasts)
    {
        $this->kAnimeCasts = $kAnimeCasts;
    }

    /**
     * Execute the job.
     *
     * @param ImportAnimeCastProcessor $importAnimeCastProcessor
     * @return void
     */
    public function handle(ImportAnimeCastProcessor $importAnimeCastProcessor): void
    {
        $importAnimeCastProcessor->process($this->kAnimeCasts);
    }
}
