<?php

namespace App\Jobs;

use App\Models\KDashboard\ProducerMagazine as KStudio;
use App\Services\ImportStudioProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportStudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of studios to process.
     *
     * @var Collection|KStudio[] $kStudios
     */
    protected Collection|array $kStudios;

    /**
     * Create a new job instance.
     *
     * @param Collection|array $kStudios
     */
    public function __construct(Collection|array $kStudios)
    {
        $this->kStudios = $kStudios;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ImportStudioProcessor $importStudioProcessor)
    {
        $importStudioProcessor->process($this->kStudios);
    }
}
