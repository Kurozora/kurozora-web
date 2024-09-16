<?php

namespace App\Jobs;

use App\Models\KDashboard\AnimeStaff as KAnimeStaff;
use App\Services\ImportAnimeStaffProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportAnimeStaff implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of people to process.
     *
     * @var Collection|KAnimeStaff[] $kAnimeStaff
     */
    protected Collection|array $kAnimeStaff;

    /**
     * Create a new job instance.
     *
     * @param Collection|array $kAnimeStaff
     */
    public function __construct(Collection|array $kAnimeStaff)
    {
        $this->kAnimeStaff = $kAnimeStaff;
    }

    /**
     * Execute the job.
     *
     * @param ImportAnimeStaffProcessor $importAnimeStaffProcessor
     * @return void
     */
    public function handle(ImportAnimeStaffProcessor $importAnimeStaffProcessor): void
    {
        $importAnimeStaffProcessor->process($this->kAnimeStaff);
    }
}
