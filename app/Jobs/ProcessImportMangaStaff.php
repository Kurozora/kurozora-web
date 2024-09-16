<?php

namespace App\Jobs;

use App\Models\KDashboard\PeopleManga as KMangaStaff;
use App\Services\ImportMangaStaffProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportMangaStaff implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of people manga to process.
     *
     * @var Collection|KMangaStaff[] $kMangaStaff
     */
    protected Collection|array $kMangaStaff;

    /**
     * Create a new job instance.
     *
     * @param Collection|array $kMangaStaff
     */
    public function __construct(Collection|array $kMangaStaff)
    {
        $this->kMangaStaff = $kMangaStaff;
    }

    /**
     * Execute the job.
     *
     * @param ImportMangaStaffProcessor $importMangaStaffProcessor
     * @return void
     */
    public function handle(ImportMangaStaffProcessor $importMangaStaffProcessor): void
    {
        $importMangaStaffProcessor->process($this->kMangaStaff);
    }
}
